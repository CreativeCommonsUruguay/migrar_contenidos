<?php

namespace Drupal\migrar_contenidos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\taxonomy\Entity\Term;

class MigrarController extends ControllerBase {

  public function content() {


  function importarcampo ($linea, $escape) {
    $resultado = array ();
    foreach (explode($escape, $linea) as $cadalinea) {
        array_push($resultado,  $cadalinea);
    }
    return ($resultado);
  }

  function importarenlace ($linea, $escape1, $escape2) {
    $resultado = array ();
    foreach (explode($escape1, $linea) as $cadalinea) {
        $lineaseparada = explode($escape2, $cadalinea);
        $enlacearmado = array ('uri' => $lineaseparada[1], 'title' => $lineaseparada[0]);
        array_push($resultado,  $cadalinea);
    }
    return ($resultado);
  }
  
 $field_enlacesmulti = $line[13];
      $field_enlaces = array ();
      foreach (explode(";", $field_enlacesmulti) as $cadaenlace) {
        $enlaceseparado = explode("|", $cadaenlace);
        $enlacearmado = array ('uri' => $enlaceseparado[1], 'title' => $enlaceseparado[0]);
        array_push($field_enlaces, $enlacearmado);
      }
      
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
            'status' => 1,
            // The user ID.
            'uid' => $uid,
            'title' => $title,
          );



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

  $fid = $line[0];
  
  $uid = $line[1];
  $uriarchivo = $line[2];

  $arreglouriarchivo = explode ("/", $uriarchivo);
  $nombrearchivo = end($arreglouriarchivo);

  $query = \Drupal::entityQuery('file')->condition('fid', $fid)->execute();

// Si no existe una entidad con esa ID, la crea.
  if (empty($query)) {


    $urldestino = 'public://2017-11/';
    $urlorigen = 'public://temp-publicaciones/';
    copy($urlorigen.$nombrearchivo, $urldestino.$nombrearchivo);


    $file = File::create([ // Crea el archivo en la base de datos
      'uid' => $uid,
      'fid' => $fid,
      'filename' => $nombrearchivo,
      'uri' => $urldestino.$nombrearchivo,
      'status' => 1,
    ]);
    $file->save();


    $resultado.= "<li>Fid: ".$fid. ", Nombre: ".$nombrearchivo." (<b>creado</b>) <br>"; }
  else { $resultado.= "<li>Fid: ".$fid. ", Nombre: ".$nombrearchivo." (<b>no creado</b>) <br>"; }

/*
        $file_usage = \Drupal::service('file.usage');
        $file_usage->add($file, 'mymodule', 'user',$uid);
        $file->save();
*/


      }
    fclose($archivo);

  
  
    $resultado.= "</ol>";
   } else { $resultado.= "No crearon archivos."; }



    $resultado.= "<hr>";

    $resultado.= "<h2>Libros</h2>";

   $fidArchivo = $config->get('idArchivoLibros');

   if (isset($fidArchivo[0])) {

    $resultado.= "<ol>";
      $file = file_load($fidArchivo[0]);
      $uri = $file->getFileUri();
      $path = file_create_url($uri);


    $archivo = fopen($uri, 'r');
      $line = fgetcsv($archivo); // Salteo la primer linea;
      while (($line = fgetcsv($archivo)) !== FALSE) {

      $title = $line[1];
      $query = \Drupal::entityQuery('node')->condition('nid', $nid)->execute();

// Si no existe una entidad con esa ID, la crea.
      if (empty($query)) {

      
        $field_autoresmulti = $line[3];
        $field_autores = array ();
        foreach (explode(";", $field_autoresmulti) as $cadaautor) {
            $autorseparado = explode("|", $cadaautor); // Ej: Alberto Nin Frías|292;Emmanuel Martínez|12811 // José Enrique Rodó|667
//            $autorname_override = $autorseparado[0];

            $autorname_override = $autorseparado[0];
            $autortarget_id = $autorseparado[1];
            $autorcategory_id = '914';
            if ($autortarget_id == '12811') { $autorcategory_id = '935'; } // si es el nodo 12811 setear el TID de autor especial (935) 
            if ($autortarget_id == '12812') { $autorcategory_id = '936'; } // si es el nodo es 12812 setear el TID de autor en dominio privado (936)
            $autorarmado = array ('target_id' => $autortarget_id, 'category_id' => $autorcategory_id, 'name_override' => $autorname_override);
            array_push ($field_autores, $autorarmado);
        }

        $field_ano_de_publicacion = $line[4];
        $field_coleccion = importarcampo ($line[5], ";");
        $field_editorial = $line[6];
        if (!empty($line[7])) { $field_temas = importarcampo ($line[7], ";"); }
        $field_enlaces_al_contenido = importarenlace ($line[8], ";", "|");
        $field_formatodearchivo = importarcampo ($line[9], ";");
        $field_idioma = importarcampo ($line[10], ";");
        $field_imprenta = $line[11];
        $field_isbn = $line[12];
        
        if (!empty($line[13])) { $field_lugardepublicacion = importarcampo ($line[13], ";"); }


        $field_portada = array ();
        if (!empty($line[14])) {
          $portadatitle = 'Portada de '.$title;
          $field_portada = array ('target_id' => $line[14], 'alt' => $portadatitle, 'title' => $portadatitle);
        }
        
        $field_numero_de_edicion = $line[15];
        $field_paginas = $line[16];
        $field_partede = $line[17];

        $libronuevo= array ();
        $libronuevo['type'] ='libro';
        $libronuevo['title'] =$title;
        $libronuevo['field_subtitulo'] =$field_subtitulo;
        $libronuevo['field_ano_de_publicacion'] =$field_ano_de_publicacion;
        if (!empty($field_coleccion)) {$libronuevo['field_coleccion'] =$field_coleccion;}
        $libronuevo['field_editorial'] =$field_editorial;
        if (!empty($field_temas)) {$libronuevo['field_temas'] =$field_temas;}
        $libronuevo['field_enlaces_al_contenido'] =$field_enlaces_al_contenido;
        if (!empty($field_formatodearchivo)) {$libronuevo['field_formatodearchivo'] =$field_formatodearchivo;}
        if (!empty($field_idioma)) {$libronuevo['field_idioma'] =$field_idioma;}
        $libronuevo['field_imprenta'] =$field_imprenta;
        $libronuevo['field_isbn'] =$field_isbn;
        if (!empty($field_lugardepublicacion)) {$libronuevo['field_lugardepublicacion'] =$field_lugardepublicacion;}
        if (!empty($field_portada)) {$libronuevo['field_portada'] =$field_portada;}
        $libronuevo['field_numero_de_edicion'] =$field_numero_de_edicion;
        $libronuevo['field_paginas'] =$field_paginas;
        $libronuevo['field_autores'] =$field_autores;

        $node = Node::create($libronuevo);

        $node->save();
        
        $resultado.= "<li>Nid: ".$nid. ", Nombre: ".$title." (<b>creado</b>) <br>";
      } else {
        $resultado.= "<li>Nid: ".$nid. ", Nombre: ".$title." (<b>no creado</b>) <br>";
      }
      
    }
    fclose($archivo);

    $resultado.= "</ol>";
   } else { $resultado.= "No se migraron libros."; }

// Obras visuales

    $resultado.= "<hr>";

    $resultado.= "<h2>Obras visuales</h2>";

   $fidArchivo = $config->get('idArchivoObrasVisuales');

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
      $query = \Drupal::entityQuery('node')->condition('nid', $nid)->execute();

// Si no existe una entidad con esa ID, la crea.
      if (empty($query)) {

/*
0. Nid
1. Titulo
2. Lista de autores - field_autores
3. Circa - field_caracteristicas_especiales
4. Año de publicación - field_ano_de_publicacion
5. Año de publicación ca - field_ano_de_publicacion
6. Digitalizado por - field_coleccion
7. Enlaces a la obra - field_enlaces_al_contenido
8. Fotografo - field_fotografo
9. Miniatura - field_portada
10. Tamaño (alto) - field_tamano_alto
11. Tamaño (ancho) - field_tamano_ancho
12. Tamaño (profundidad) - field_tamano_profundidad

13. Tipo de obra - field_tipo_de_obra_visual
14. Técnica / materiales - field_materiales
15. Origen - field_origen
16. Ubicación - field_ubicacion
17. Calidad de digitalización - field_calidad_de_digitalizacion

*/


        $field_autoresmulti = $line[2];
        $field_autores = array ();
        foreach (explode(";", $field_autoresmulti) as $cadaautor) {
            $autorseparado = explode("|", $cadaautor); // Ej: Alberto Nin Frías|292;Emmanuel Martínez|12811 // José Enrique Rodó|667
            $autorname_override = $autorseparado[0];
            $autortarget_id = $autorseparado[1];
            $autorcategory_id = '914';
            $autorarmado = array ('target_id' => $autortarget_id, 'category_id' => $autorcategory_id, 'name_override' => $autorname_override);
            array_push ($field_autores, $autorarmado);
        }
        
        $field_circa = importarcampo ($line[3], ";");
        $field_ano_de_publicacion = importarcampo ($line[4], ";");
        if (!empty($line[5])) { array_push($field_ano_de_publicacion, $line[5]); };

        $field_coleccion = importarcampo ($line[6], ";");
        $field_enlaces_al_contenido = importarenlace ($line[7], ";", "|");
        $field_fotografo = importarcampo ($line[8], ";");
        
        $field_portada = array ();
          if (!empty($line[9])) {
            $autorparatitulo = explode("|", $line[2]); 
            $portadatitle = "'".$title."' de ".$autorparatitulo[0];
            $field_portada = array ('target_id' => $line[9], 'alt' => $portadatitle, 'title' => $portadatitle);
          }
        

        $field_tamano_alto = importarcampo ($line[10], ";");
        $field_tamano_ancho = importarcampo ($line[11], ";");
        $field_tamano_profundidad = importarcampo ($line[12], ";");

        $field_tipo_de_obra_visual = importarcampo ($line[13], ";");
        $field_materiales = importarcampo ($line[14], ";");
        $field_origen = importarcampo ($line[15], ";");
        $field_ubicacion = importarcampo ($line[16], ";");
        $field_calidad_de_digitalizacion = importarcampo ($line[17], ";");


        $obravisualnueva= array ();
        $obravisualnueva['nid'] =$nid;
        $obravisualnueva['type'] ='obra_visual';
        $obravisualnueva['title'] =$title;
        $obravisualnueva['field_autores'] =$field_autores;
        if (!empty(array_filter($field_circa))) {$obravisualnueva['field_caracteristicas_especiales'] =$field_circa;}
        if (!empty($field_ano_de_publicacion)) {$obravisualnueva['field_ano_de_publicacion'] =$field_ano_de_publicacion;} 
        if (!empty(array_filter($field_coleccion))) {$obravisualnueva['field_coleccion'] =$field_coleccion;} 
        if (!empty($field_enlaces_al_contenido)) {$obravisualnueva['field_enlaces_al_contenido'] =$field_enlaces_al_contenido;} 
        if (!empty($field_fotografo)) {$obravisualnueva['field_fotografo'] =$field_fotografo;} 
        if (!empty($field_portada)) {$obravisualnueva['field_portada'] =$field_portada;}
        if (!empty($field_tamano_alto)) {$obravisualnueva['field_tamano_alto'] =$field_tamano_alto;}
        if (!empty($field_tamano_ancho)) {$obravisualnueva['field_tamano_ancho'] =$field_tamano_ancho;}
        if (!empty($field_tamano_profundidad)) {$obravisualnueva['field_tamano_profundidad'] =$field_tamano_profundidad;}
        if (!empty(array_filter($field_tipo_de_obra_visual))) {$obravisualnueva['field_tipo_de_obra_visual'] =$field_tipo_de_obra_visual;}
        if (!empty($field_materiales)) {$obravisualnueva['field_materiales'] =$field_materiales;}
        if (!empty($field_origen)) {$obravisualnueva['field_origen'] =$field_origen;}
        if (!empty($field_ubicacion)) {$obravisualnueva['field_ubicacion'] =$field_ubicacion;}
        if (!empty(array_filter($field_calidad_de_digitalizacion))) {$obravisualnueva['field_calidad_de_digitalizacion'] =$field_calidad_de_digitalizacion;}
        
        
        $node = Node::create($obravisualnueva);

        $node->save();
        
        $resultado.= "<li>Nid: ".$nid. ", Nombre: ".$title." (<b>creado</b>) <br>";
      } else {
        $resultado.= "<li>Nid: ".$nid. ", Nombre: ".$title." (<b>no creado</b>) <br>";
      }
      
    }
    fclose($archivo);

    $resultado.= "</ol>";
   } else { $resultado.= "No se migraron obras visuales."; }


    $resultado.= "<hr>";

    $fidArchivo = $config->get('idArchivoAutoresWD');
    $resultado.= "<h2>ID Wd</h2>";

   if (isset($fidArchivo[0])) {

    $resultado.= "<ol>";

      $file = file_load($fidArchivo[0]);
      $uri = $file->getFileUri();
      $path = file_create_url($uri);


    $archivo = fopen($uri, 'r');
      $line = fgetcsv($archivo); // Salteo la primer linea;
      while (($line = fgetcsv($archivo)) !== FALSE) {

      $IDautores = $line[0];
      $IDWikidata = $line[1];

           $entidad = \Drupal::entityTypeManager()->getStorage('node')->load($IDautores);

        if (!empty($entidad)) {
            $valorPrevio = $entidad->get('field_id_wikidata')->getValue();
            if (empty($valorPrevio)) {
    		    $entidad->set('field_id_wikidata', $IDWikidata);
	            $entidad->save();
          	    $resultado.= "<li>ID Wikidata: ".$IDWikidata." (<b>agregada al nodo</b>) <br>";
            } else {
                          	    $resultado.= "<li>ID Wikidata: ".$IDWikidata." (<b>ya existía en el nodo</b>) <br>";
            }
        }
      }
    fclose($archivo);

    $resultado.= "</ol>";
   } else { $resultado.= "No se agregaron IDs."; }
   
      
      
    $arreglo['#title'] = 'Resultado de la migración';
    $arreglo['#theme'] = "vista";

   $arreglo['#resultadoMigracion'] = array(
      '#markup' => $resultado,
      '#allowed_tags' => ['li', 'br', 'b', 'ol', 'h2', 'hr'],
    );

    return $arreglo;

  }


}
