<?php

namespace Drupal\migrar_contenidos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\taxonomy\Entity\Term;

class MigrarController extends ControllerBase {

  public function content() {

    $config = \Drupal::service('config.factory')->getEditable('migrar_contenidos.settings');

    $resultado = "";

// Migración de usuarios

    $fidArchivo = $config->get('idArchivoUsuarios');
    $resultado.= "<h2>Usuarios</h2>";

   if (isset($fidArchivo[0])) {

    $resultado.= "<ol>";

      $file = file_load($fidArchivo[0]);
      $uri = $file->getFileUri();
      $path = file_create_url($uri);


    $archivo = fopen($uri, 'r');
      $line = fgetcsv($archivo); // Salteo la primer linea;
      while (($line = fgetcsv($archivo)) !== FALSE) {

      $nombre = $line[0];
      $email = $line[1];
      $rolesID = $line[2];
      if ($rolesID == "4") { $roles = "creador_contenidos"; }
      if ($rolesID == "1") { $roles = "administrator"; }

      $uid = $line[3];
      $registrado = $line[4];
      $ultimoacceso = $line[5];

        $query = \Drupal::entityQuery('user')->condition('uid', $uid)->execute();

// Si no existe una entidad con esa ID, la crea.
        if (empty($query)) {

          $lang = \Drupal::languageManager()->getCurrentLanguage()->getId();

          $user = User::create([
            'uid' => $uid,
            'name' => $nombre,
            'pass' => $nombre,
            'mail' => $email,
            'roles' => $roles,
            'created' => $registrado,
          ]);

          $user->setLastAccessTime($ultimoacceso);
          $user->activate();
          $user->save();
          $resultado.= "<li>Usuario: ".$nombre." (<b>creado</b>) <br>";
        } else {
          $resultado.= "<li>Usuario: ".$nombre." (<b>no creado</b>) <br>"; }

      }
    fclose($archivo);

    $resultado.= "</ol>";
   } else { $resultado.= "No se migraron usuarios."; }

    $resultado.= "<hr>";


// Migración de taxonomias

    $fidArchivo = $config->get('idArchivoTaxonomias');
    $resultado.= "<h2>Taxonomias</h2>";

   if (isset($fidArchivo[0])) {

    $resultado.= "<ol>";

      $file = file_load($fidArchivo[0]);
      $uri = $file->getFileUri();
      $path = file_create_url($uri);

    $archivo = fopen($uri, 'r');
      $line = fgetcsv($archivo); // Salteo la primer linea;
      $nombrecampos = array ();


      foreach ($line as $cadalinea) {
        array_push($nombrecampos, $cadalinea);
        }

      while (($line = fgetcsv($archivo)) !== FALSE) {

      $contenidocampos = array ();
      foreach ($line as $cadalinea) {
        array_push($contenidocampos, $cadalinea);
        }

      $terminos = Term::loadMultiple();


        $terminonuevo = array ();
        foreach ($contenidocampos as $key => $cadacampo) {
          $terminonuevo[$nombrecampos[$key]] = $cadacampo;
        }
          $terminonuevo['langcode'] = 'es';
        $tid = $terminonuevo['tid'];
        $nombre = $terminonuevo['name'];
        $vocabulario = $terminonuevo['vid'];

      if (!isset($terminos[$tid])) {


        $term = Term::create($terminonuevo);
        $term->save();
        $resultado.= "<li>Taxonomia '".$nombre."' del vocabulario '".$vocabulario."' creada";
      } else {
        $resultado.= "<li>Taxonomia '".$nombre."' del vocabulario '".$vocabulario."' ya estaba creada";
      }
    }
    fclose($archivo);

    $resultado.= "</ol>";
   } else { $resultado.= "No se migraron taxonomías."; }

    $resultado.= "<hr>";

    $resultado.= "<h2>Autores</h2>";


    $fidArchivo = $config->get('idArchivoContenidos');

   if (isset($fidArchivo[0])) {

    $resultado.= "<ol>";
      $file = file_load($fidArchivo[0]);
      $uri = $file->getFileUri();
      $path = file_create_url($uri);


    $archivo = fopen($uri, 'r');
      $line = fgetcsv($archivo); // Salteo la primer linea;
      while (($line = fgetcsv($archivo)) !== FALSE) {

      $nid = $line[0];
      $title = $line[1];
      $field_apellidos = $line[2];
      $field_nombres = $line[3];
      $field_variantes_de_nombre = $line[4];
      $field_seudonimos = $line[5];

      $diaymesnac = explode ("/", $line[6]);
      $field_dia_de_nacimiento = $diaymesnac[0];
      $field_mes_de_nacimiento = $diaymesnac[1];
      $field_ano_de_nacimiento = $line[7];

      $diaymesmuer = explode ("/", $line[8]);
      $field_dia_de_muerte = $diaymesmuer[0];
      $field_mes_de_muerte = $diaymesmuer[1];
      $field_ano_de_muerte = $line[9];

      $field_sexotids = $line[10];
      $field_sexo = array ();
      array_push($field_sexo, $field_sexotids);

      $field_disciplinaautoraltids = $line[11];
      $field_disciplinaautoral = array ();
      foreach (explode(";", $field_disciplinaautoraltids) as $cadadisciplina) {
        array_push($field_disciplinaautoral, $cadadisciplina);
      }

      $field_lugartids = $line[12];
      $field_lugardenacimiento = array ();
      array_push($field_lugardenacimiento, $field_lugartids);

      $field_enlacesmulti = $line[13];
      $field_enlaces = array ();
      foreach (explode(";", $field_enlacesmulti) as $cadaenlace) {
        $enlaceseparado = explode("|", $cadaenlace);
        $enlacearmado = array ('uri' => $enlaceseparado[1], 'title' => $enlaceseparado[0]);
        array_push($field_enlaces, $enlacearmado);
      }

      $field_fuentestids = $line[14];
      $field_fuentes = array ();
      foreach (explode(";", $field_fuentestids) as $cadafuente) {
        array_push($field_fuentes, $cadafuente);
      }

      $field_notasmulti = $line[15];
      $field_notas = array ();
      foreach (explode(";", $field_notasmulti) as $cadanota) {
        array_push($field_notas, $cadanota);
      }

      $created = $line[16];
      $uid = $line[17];

/* Faltan levantar los siguientes campos desde el csv
field_otrasdisciplinas [?]
*/

        $query = \Drupal::entityQuery('node')->condition('nid', $nid)->execute();

// Si no existe una entidad con esa ID, la crea.
        if (empty($query)) {

        $nodonuevo = array (
            'nid' => $nid,
            'type' => 'autor',
            'langcode' => 'es',
            'field_nombres' => $field_nombres,
            'field_apellidos' => $field_apellidos,
            'field_variantes_de_nombre' => $field_variantes_de_nombre,
            'field_seudonimos' => $field_seudonimos,
            'field_dia_de_nacimiento' => $field_dia_de_nacimiento,
            'field_mes_de_nacimiento' => $field_mes_de_nacimiento,
            'field_ano_de_nacimiento' => $field_ano_de_nacimiento,
            'field_dia_de_muerte' => $field_dia_de_muerte,
            'field_mes_de_muerte' => $field_mes_de_muerte,
            'field_ano_de_muerte' => $field_ano_de_muerte,
            'field_sexo' => $field_sexo,
            'field_disciplinaautoral' => $field_disciplinaautoral,
            'field_lugardenacimiento' => $field_lugardenacimiento,
            'field_enlaces' => $field_enlaces,
            'field_notas' => $field_notas,
            'created' => $created,
            'changed' => REQUEST_TIME,
            // The user ID.
            'uid' => $uid,
            'title' => $title,
          );

       if (!empty($field_fuentes)) {
         array_push ($nodonuevo, array('field_fuentes' => $field_fuentes));
       }

          $node = Node::create($nodonuevo);

          $node->save();
          $resultado.= "<li>Nid: ".$nid. ", Nombre: ".$title." (<b>creado</b>) <br>";

        } else {
          $resultado.= "<li>Nid: ".$nid. ", Nombre: ".$title." (<b>no creado</b>) <br>";
        }

      }
    fclose($archivo);

    $resultado.= "</ol>";
   } else { $resultado.= "No se migraron autores."; }



    $resultado.= "<hr>";

    $resultado.= "<h2>Archivos</h2>";



    $fidArchivo = $config->get('idArchivoArchivos');

   if (isset($fidArchivo[0])) {

    $resultado.= "<ol>";
      $file = file_load($fidArchivo[0]);
      $uri = $file->getFileUri();
      $path = file_create_url($uri);


    $archivo = fopen($uri, 'r');
      $line = fgetcsv($archivo); // Salteo la primer linea;
      while (($line = fgetcsv($archivo)) !== FALSE) {

/* Copia el archivo desde la URL orígen al nuevo directorio */
  $uid = 1;
  $fid = $line[0];
  $uriarchivo = $line[1];

  $arreglouriarchivo = explode ("/", $uriarchivo);
  $nombrearchivo = end($arreglouriarchivo);
  $urldestino = 'public://2017-03/';
  $urlorigen = 'http://autores.uy/sites/default/files/';
  copy($urlorigen.$nombrearchivo, $urldestino.$nombrearchivo);

/* Crea el archivo en la base de datos */
  $file = File::create([
    'uid' => $uid,
    'fid' => $fid,
    'filename' => $nombrearchivo,
    'uri' => $urldestino.$nombrearchivo,
    'status' => 1,
  ]);
  $file->save();



/*
        $file_usage = \Drupal::service('file.usage');
        $file_usage->add($file, 'mymodule', 'user',$uid);
        $file->save();
*/
/*
      $nid = $line[0];
      $title = $line[1];
      $field_apellidos = $line[2];
      $field_nombres = $line[3];
      $field_variantes_de_nombre = $line[4];
      $field_seudonimos = $line[5];

        $nodonuevo = array (
            'nid' => $nid,
            'type' => 'autor',
            'langcode' => 'es',
            'field_nombres' => $field_nombres,
            'field_apellidos' => $field_apellidos,
            'field_variantes_de_nombre' => $field_variantes_de_nombre,
            'field_seudonimos' => $field_seudonimos,
            'field_dia_de_nacimiento' => $field_dia_de_nacimiento,
            'field_mes_de_nacimiento' => $field_mes_de_nacimiento,
            'field_ano_de_nacimiento' => $field_ano_de_nacimiento,
            'field_dia_de_muerte' => $field_dia_de_muerte,
            'field_mes_de_muerte' => $field_mes_de_muerte,
            'field_ano_de_muerte' => $field_ano_de_muerte,
            'field_sexo' => $field_sexo,
            'field_disciplinaautoral' => $field_disciplinaautoral,
            'field_lugardenacimiento' => $field_lugardenacimiento,
            'field_enlaces' => $field_enlaces,
            'field_notas' => $field_notas,
            'created' => $created,
            'changed' => REQUEST_TIME,
            // The user ID.
            'uid' => $uid,
            'title' => $title,
          );

          $node = Node::create($nodonuevo);

          $node->save();

*/

/* TODO Para la sección de crear libros 
$node = Node::create([
  'type'        => 'libro',
  'title'       => 'Druplicon test',
  'field_portada' => [
    'target_id' => 710,
    'alt' => 'Hello world',
    'title' => 'Goodbye world'
  ],
]);
          $node->save();


*/

      }
    fclose($archivo);

    $resultado.= "</ol>";
   } else { $resultado.= "No crearon archivos."; }




    $arreglo['#title'] = 'Resultado de la migración';
    $arreglo['#theme'] = "vista";

   $arreglo['#resultadoMigracion'] = array(
      '#markup' => $resultado,
      '#allowed_tags' => ['li', 'br', 'b', 'ol', 'h2', 'hr'],
    );

    return $arreglo;

  }


}
