<?php
/**
 * Created by PhpStorm.
 * User: Raul
 * Date: 24/10/2018
 * Time: 11:13
 */
require_once '../model/NoticiasModel.php';
$noticia = new NoticiasModel();
if (!session_id()) session_start();

/*Salvando Notícia*/
if (!empty($_POST['acao']) and ($_POST['acao'] == 1)){
    /*Definindo zona de tempo*/
    date_default_timezone_set('America/Sao_Paulo');

    /*Função para formatar a imagem da noticia*/
    function tratar_arquivo_upload($string){
        // pegando a extensao do arquivo
        $partes 	= explode(".", $string);
        $extensao 	= $partes[count($partes)-1];
        // somente o nome do arquivo
        $nome	= preg_replace('/\.[^.]*$/', '', $string);
        // removendo simbolos, acentos etc
        $a = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýýþÿŔŕ?';
        $b = 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuuyybyRr-';
        $nome = strtr($nome, utf8_decode($a), $b);
        $nome = str_replace(".","-",$nome);
        $nome = preg_replace( "/[^0-9a-zA-Z\.]+/",'-',$nome);
        return utf8_decode(strtolower($nome.".".$extensao));
    }


    $autor = $_SESSION['nome'];
    $dataPublicacao = date('Y-m-d H:i:s');
    $tituloNoticia = $_POST['tituloNoticia'];
    $descricaoNoticia = $_POST['descricao'];
    $corpoNoticia = $_POST['corpo'];

    $file = $_FILES['fundoNoticia'];
    $filename = tratar_arquivo_upload(utf8_decode($file['name']));
    $uploaddir = '../view/img/noticias/';
    $uploadfile = $uploaddir . basename($filename);


    $noticia->setAutor($autor);
    $noticia->setCorpoNoticia($corpoNoticia);
    $noticia->setTituloNoticia($tituloNoticia);
    $noticia->setDescricaoNoticia($descricaoNoticia);
    $noticia->setDataPubicacao($dataPublicacao);
    $noticia->setIsPublicada(0);
    if (move_uploaded_file($_FILES['fundoNoticia']['tmp_name'], $uploadfile)) {
        $noticia->setFundoNoticia($filename);
    }

    if ($noticia->insert()){
        $_SESSION['salvou'] = true;
        header('location:../view/adm/noticias-gestao.php');
    }else{
        $_SESSION['salvou'] = false;
        header('location:../view/adm/noticias-gestao.php');
    }
}
/*Excluindo Notícia*/
if (!empty($_GET['acao']) and $_GET['acao'] == 2){
    $codigoNoticia = $_SESSION['excluir'];

    if ($noticia->deletar($codigoNoticia)){
        $_SESSION['excluiu'] = true;
        $_SESSION['excluir'] = null;
        header('location:../view/adm/noticias-gestao.php');
    }else{
        $_SESSION['excluiu'] = false;
        $_SESSION['excluir'] = null;
        header('location:../view/adm/noticias-gestao.php');
    }

}

/*Editando notícia*/
if (!empty($_POST['acao']) and ($_POST['acao'] == 3)){

    date_default_timezone_set('America/Sao_Paulo');

    /*Função para formatar a imagem da noticia*/
    function tratar_arquivo_upload($string){
        // pegando a extensao do arquivo
        $partes 	= explode(".", $string);
        $extensao 	= $partes[count($partes)-1];
        // somente o nome do arquivo
        $nome	= preg_replace('/\.[^.]*$/', '', $string);
        // removendo simbolos, acentos etc
        $a = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýýþÿŔŕ?';
        $b = 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuuyybyRr-';
        $nome = strtr($nome, utf8_decode($a), $b);
        $nome = str_replace(".","-",$nome);
        $nome = preg_replace( "/[^0-9a-zA-Z\.]+/",'-',$nome);
        return utf8_decode(strtolower($nome.".".$extensao));
    }


    $autor = $_SESSION['nome'];
    $codigoNoticia = $_POST['codigoNoticia'];
    $ultimaAtualizacao = date('Y-m-d H:i:s');
    $tituloNoticia = $_POST['tituloNoticia'];
    $descricaoNoticia = $_POST['descricao'];
    $corpoNoticia = $_POST['corpo'];

    $noticiaSelecionada = $noticia->selectPorCodigo($codigoNoticia);
    $isPublicada = $noticiaSelecionada->isPublicada;

    if (!$_FILES['fundoNoticia']['size'] == 0){

        $noticiaSelecionada = $noticia->selectPorCodigo($codigoNoticia);
        $file =  '../view/img/noticias/'.$noticiaSelecionada->fundoNoticia;

        if (file_exists($file)){
            unlink($file);
            $file = $_FILES['fundoNoticia'];
            $filename = tratar_arquivo_upload(utf8_decode($file['name']));
            $uploaddir = '../view/img/noticias/';
            $uploadfile = $uploaddir . basename($filename);
            move_uploaded_file($_FILES['fundoNoticia']['tmp_name'], $uploadfile);
            $noticia->setFundoNoticia($filename);
        }
    }else{
        $noticiaSelecionada = $noticia->selectPorCodigo($codigoNoticia);
        $noticia->setFundoNoticia($noticiaSelecionada->fundoNoticia);
    }




    $noticia->setAutor($autor);
    $noticia->setCorpoNoticia($corpoNoticia);
    $noticia->setTituloNoticia($tituloNoticia);
    $noticia->setDescricaoNoticia($descricaoNoticia);
    $noticia->setUltimaAtualizacao($ultimaAtualizacao);
    $noticia->setIsPublicada($isPublicada);

    if ($noticia->updateNoticia($codigoNoticia)){
        $_SESSION['editou'] = true;
        header('location:../view/adm/noticias-gestao.php');
    }else{
        $_SESSION['editou'] = false;
        header('location:../view/adm/noticias-gestao.php');
    }
}

/*Publicando notícia*/
if (!empty($_GET['acao']) and  $_GET['acao'] == 4){
    $codigoNoticia = $_SESSION['publicar'];
    $isPublicada = $_SESSION['isPublicada'];
    if ($noticia->publicaNoticia($codigoNoticia,$isPublicada)){
        $_SESSION['publicou'] = true;
        $_SESSION['publicar'] = null;
        $_SESSION['isPublicada'] = null;
        header('location:../view/adm/noticias-gestao.php');
    }else{
        $_SESSION['publicou'] = false;
        $_SESSION['publicar'] = null;
        $_SESSION['isPublicada'] = null;
        header('location:../view/adm/noticias-gestao.php');
    }
}

/*Publicando notícia via ajax*/
if (!empty($_POST['codigoNoticiaPublicar'])){
    $codigoNoticia = $_POST['codigoNoticiaPublicar'];
    $noticia->publicaNoticia($codigoNoticia,1);
    echo $codigoNoticia;
}

/*Opções de paginação*/

if (!empty($_POST['exibirPor']) && ($_POST['exibirPor'] == 'dataPublicacao')) {

    $orderBY = $_POST['exibirPor'];
    $order = $_POST['classificarPor'];

    if (empty($_POST['pagina'])){
        $pagina = 1;
    }else{
        $pagina = $_POST['pagina'];
    }


    $cont =1;
    foreach ( $noticia->selectAllByJSON($orderBY,$order,$pagina) as $key => $noticiaAtual){

        if ($noticiaAtual->fundoNoticia == null) {
            $fundo = 'img/noticias/fundo-neutro.png';
        } else {
            $fundo = "img/noticias/".$noticiaAtual->fundoNoticia;
        }
        if ($cont <= 5) {

            echo "<a href='noticia.php?noticia=$noticiaAtual->codigoNoticia' class='noticia-abertura col-11 col-md-3 p-0 m-2 position-relative'>";
            echo "<img src='$fundo' alt='Fundo da notícia' class='img-fluid w-100'>";
            echo "<div class='info-noticia px-1'>";
            echo "<h5 class='noticia-titulo'>$noticiaAtual->tituloNoticia</h5>";
            echo "<p class='noticia-descricao'>$noticiaAtual->descricaoNoticia</p>";
            echo "</div>";
            echo "</a>";

        }
        if ($cont > 5) {
            echo "<a href='noticia.php?noticia=$noticiaAtual->codigoNoticia' class='noticia-abertura d-none d-md-block col-11 col-md-3 p-0 m-2 position-relative'>";
            echo "<img src='$fundo' alt='Fundo da notícia' class='img-fluid w-100'>";
            echo "<div class='info-noticia px-1'>";
            echo "<h5 class='noticia-titulo'>$noticiaAtual->tituloNoticia</h5>";
            echo "<p class='noticia-descricao'>$noticiaAtual->descricaoNoticia</p>";
            echo "</div>";
            echo "</a>";
        }

        $cont++;


    }
}

if (!empty($_POST['exibirPor']) && ($_POST['exibirPor'] == 'numAcessos')){
    $orderBY = $_POST['exibirPor'];

    if (empty($_POST['pagina'])){
        $pagina = 1;
    }else{
        $pagina = $_POST['pagina'];
    }
    $cont =1;
    foreach ( $noticia->selectAllByJSON($orderBY,'DESC',$pagina) as $key => $noticiaAtual){

        if ($noticiaAtual->fundoNoticia == null) {
            $fundo = 'img/noticias/fundo-neutro.png';
        } else {
            $fundo = "img/noticias/".$noticiaAtual->fundoNoticia;
        }
        if ($cont <= 5) {

            echo "<a href='noticia.php?noticia=$noticiaAtual->codigoNoticia' class='noticia-abertura col-11 col-md-3 p-0 m-2 position-relative'>";
            echo "<img src='$fundo' alt='Fundo da notícia' class='img-fluid w-100'>";
            echo "<div class='info-noticia px-1'>";
            echo "<h5 class='noticia-titulo'>$noticiaAtual->tituloNoticia</h5>";
            echo "<p class='noticia-descricao'>$noticiaAtual->descricaoNoticia</p>";
            echo "</div>";
            echo "</a>";

        }
        if ($cont > 5) {
            echo "<a href='noticia.php?noticia=$noticiaAtual->codigoNoticia' class='noticia-abertura d-none d-md-block col-11 col-md-3 p-0 m-2 position-relative'>";
            echo "<img src='$fundo' alt='Fundo da notícia' class='img-fluid w-100'>";
            echo "<div class='info-noticia px-1'>";
            echo "<h5 class='noticia-titulo'>$noticiaAtual->tituloNoticia</h5>";
            echo "<p class='noticia-descricao'>$noticiaAtual->descricaoNoticia</p>";
            echo "</div>";
            echo "</a>";
        }

        $cont++;


    }

}
