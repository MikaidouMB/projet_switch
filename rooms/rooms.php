<?php

// if(!user_is_admin()) {
// 	header('location:' . URL . 'login.php');
// 	exit(); // bloque l'exécution du code 
// }
var_dump($_POST);
//*********************************************************************
//*********************************************************************
// SUPPRESSION D'UN ARTICLE
//*********************************************************************
//*********************************************************************






if (isset($_GET['action']) && $_GET['action'] == 'supprimer' && !empty($_GET['id_salle'])) {
    $suppression = $pdo->prepare("DELETE FROM salle WHERE id_salle = :id_salle");
    $suppression->bindParam(":id_salle", $_GET['id_salle'], PDO::PARAM_STR);
    $suppression->execute();

    $msg .= '<div class="validation bg-success">Suppression du produit : ' . $_GET['id_salle'] . '</div>';


    $_GET['action'] = 'affichage'; // pour provoquer l'affichage du tableau

}
//*********************************************************************
//*********************************************************************
// \FIN SUPPRESSION D'UN ARTICLE
//*********************************************************************
//il faut que l'on puisse afficher le insert de salle dans la bdd à partir du moment ou on modifie
//cliquer sur modifier permet d'inserer l'apparition du champ->id_salle
//*********************************************************************

$id_salle = ''; // pour la modification
$titre = "";
$description = "";
$photo_bdd = "";
$pays = "";
$ville = "";
$adresse = "";
$cp = "";
$capacite = "";
$categorie = "";


//*********************************************************************
//*********************************************************************
// ENREGISTREMENT & MODIFICATION DES ARTICLES
//*********************************************************************
//*********************************************************************

if (
    isset($_POST['id_salle']) &&
    isset($_POST['titre']) &&
    isset($_POST['description']) &&
    isset($_POST['pays']) &&
    isset($_POST['ville']) &&
    isset($_POST['adresse']) &&
    isset($_POST['cp']) &&
    isset($_POST['capacite']) &&
    isset($_POST['categorie'])
) {

    $id_salle = trim($_POST['id_salle']);
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $pays = trim($_POST['pays']);
    $ville = trim($_POST['ville']);
    $adresse = trim($_POST['adresse']);
    $cp = trim($_POST['cp']);
    $capacite = trim($_POST['capacite']);
    $categorie = trim($_POST['categorie']);

    // récupération de la photo actuelle pour les modifs
    if (!empty($_POST['photo_actuelle'])) {
        $photo_bdd = $_POST['photo_actuelle'];
    }
    if (empty($cp) || !is_numeric($cp)) {
        $msg .= '<div class="alert alert-danger mt-3">Attention, le code postal est obligatoire et doit être numérique.</div>';
    }

    // controle sur la id salle car elle est unique en BDD
    $verif_reference = $pdo->prepare("SELECT * FROM salle WHERE id_salle = :id_salle");
    $verif_reference->bindParam(':id_salle', $id_salle, PDO::PARAM_STR);
    $verif_reference->execute();

    // si on a une ligne, alors la reference existe en bdd
    // on ne vérifie la référence que lors d'un ajout. Si $id_salle est vide alors c'est un ajout sinon c'est une modif.
    if ($verif_reference->rowCount() > 0 && empty($id_salle)) {
        $msg .= '<div class="alert alert-danger mt-3">Attention, référence indisponible car déjà attribuée.</div>';
    } //else {
    // vérification du format de l'image, formats accèptés : jpg, jpeg, png, gif
    // est-ce qu'une image a été posté : 
    if (!empty($_FILES['photo']['name'])) {

        //     // on vérifie le format de l'image en récupérant son extension
        $extension = strrchr($_FILES['photo']['name'], '.');
        //     // strrchr() découpe une chaine fournie en premier argument en partant de la fin. On remonte jusqu'au caractère fourni en deuxième argument et on récupère tout depuis ce caractère.
        //     // exemple strrchr('image.png', '.'); => on récupère .png
        var_dump($extension);

        //     // on enlève le point et on passe l'extension en minuscule pour pouvoir la comparer.
        $extension = strtolower(substr($extension, 1));
        //     // exemple : .PNG => png    .Jpeg => jpeg

        //     // on déclare un tableau array contenant les extensions autorisées :
        $tab_extension_valide = array('png', 'gif', 'jpg', 'jpeg');

        //     // in_array(ce_quon_cherche, tableau_ou_on_cherche);
        //     // in_array() renvoie true si le premier argument correspond à une des valeurs présentes dans le tableau array fourni en deuxième argument. Sinon false
        $verif_extension = in_array($extension, $tab_extension_valide);

        if ($verif_extension) {

            //         // pour ne pas écraser une image du même nom, on renomme l'image en rajoutant la référence qui est une information unique
            $nom_photo = $_FILES['photo']['name'];

            $photo_bdd = $nom_photo; // représente l'insertion en BDD

            //         // on prépare le chemin où on va enregistrer l'image
            //                $photo_dossier = SERVER_ROOT . SITE_ROOT . 'img/' . $nom_photo;
            $photo_dossier = '../img/' . $nom_photo;
            var_dump($photo_dossier);

            //         // copy(); permet de copier un fichier depuis un emplacement fourni en premier argument vers un emplacement fourni en deuxième
            copy($_FILES['photo']['tmp_name'], $photo_dossier);
        } else {
            $msg .= '<div class="alert alert-danger mt-3">Attention, le format de la photo est invalide, extensions autorisées : jpg, jpeg, png, gif.</div>';
        }
    }


    // on peut déclencher l'enregistrement s'il n'y a pas eu d'erreur dans les traitements précédents
    if (empty($msg)) {
        echo 'IF EMPTY MESSAGE <br>';
        if (!empty($id_salle)) {
            echo 'IF EMPTY ID SALLE <br>';
            // si $id_salle n'est pas vide c'est un UPDATE
            $enregistrement = $pdo->prepare("UPDATE salle SET titre = :titre, description = :description, photo = :photo, pays = :pays, ville = :ville, adresse = :adresse, cp = :cp, capacite = :capacite,  categorie = :categorie WHERE id_salle = :id_salle");
            // on rajoute le bindParam pour l'id_salle car => modification
            $enregistrement->bindParam(":id_salle", $id_salle, PDO::PARAM_STR);
        } else {
            echo 'IF EMPTY ID SALLE INSERT <br>';
            // sinon un INSERT
            $enregistrement = $pdo->prepare("INSERT INTO salle
         (titre, categorie, description, photo, pays, ville, adresse, cp, capacite) 
        VALUES (:titre, :categorie, :description, :photo, :pays, :ville, :adresse, :cp, :capacite)");
        }
        $enregistrement->bindParam(":titre", $titre, PDO::PARAM_STR);
        $enregistrement->bindParam(":description", $description, PDO::PARAM_STR);
        $enregistrement->bindParam(":photo", $photo_bdd, PDO::PARAM_STR);
        $enregistrement->bindParam(":pays", $pays, PDO::PARAM_STR);
        $enregistrement->bindParam(":ville", $ville, PDO::PARAM_STR);
        $enregistrement->bindParam(":adresse", $adresse, PDO::PARAM_STR);
        $enregistrement->bindParam(":cp", $cp, PDO::PARAM_STR);
        $enregistrement->bindParam(":capacite", $capacite, PDO::PARAM_STR);
        $enregistrement->bindParam(":categorie", $categorie, PDO::PARAM_STR);
        $enregistrement->execute();
    }
}
//}
//*********************************************************************
//*********************************************************************
// \FIN ENREGISTREMENT DES ARTICLES
//*********************************************************************
//*********************************************************************


//*********************************************************************
//*********************************************************************
// MODIFICATION : RECUPERATION DES INFOS DE L'ARTICLE EN BDD
//*********************************************************************
//*********************************************************************

if (isset($_GET['action']) && $_GET['action'] == 'modifier' && !empty($_GET['id_salle'])) {

    $infos_salle = $pdo->prepare("SELECT * FROM salle WHERE id_salle = :id_salle");
    $infos_salle->bindparam(":id_salle", $_GET['id_salle'], PDO::PARAM_STR);
    $infos_salle->execute();

    if ($infos_salle->rowCount() > 0) {
        $salle_actuelle = $infos_salle->fetch(PDO::FETCH_ASSOC);

        $id_salle = $salle_actuelle['id_salle'];
        $titre = $salle_actuelle['titre'];
        $description = $salle_actuelle['description'];
        $pays = $salle_actuelle['pays'];
        $taille = $salle_actuelle['ville'];
        $adresse = $salle_actuelle['adresse'];
        $photo_actuelle = $salle_actuelle['photo'];
        $cp = $salle_actuelle['cp'];
        $capacite = $salle_actuelle['capacite'];
        $categorie = $salle_actuelle['categorie'];
    }
}


//*********************************************************************
//*********************************************************************
// \FIN MODIFICATION : RECUPERATION DES INFOS DE L'ARTICLE EN BDD
//*********************************************************************
//*********************************************************************

//***************************
// AFFICHAGE DES ARTICLES
//***************************
?>
<div class="starter-template">
    <h1><i class="fas fa-ghost" style="color: #4c6ef5;"></i> Gestion salle <i class="fas fa-ghost" style="color: #4c6ef5;"></i></h1>
    <p class="lead"><?php echo $msg; ?></p>

    <p class="text-center">
        <a href="?action=ajouter" class="btn btn-outline-danger">Ajout article</a>
        <a href="?action=affichage" class="btn btn-outline-primary">Affichage article</a>
    </p>

</div>

<?php

if (isset($_GET['action']) && $_GET['action'] == 'affichage') {
    // on récupère les salle en bdd
    $liste_salle = $pdo->query("SELECT * FROM salle");

    echo '<p>Nombre d\'article : <b>' . $liste_salle->rowCount() . '</b></p>';

    echo '<div class="table-responsive">';
    echo '<table class="table table-bordered">';
    echo '<tr>';
    echo '<th>Id salle</th>';
    echo '<th>Titre</th>';
    echo '<th>Description</th>';
    echo '<th>Photo</th>';
    echo '<th>pays</th>';
    echo '<th>ville</th>';
    echo '<th>adresse</th>';
    echo '<th>cp</th>';
    echo '<th>capacite</th>';
    echo '<th>Catégorie</th>';
    echo '<th>Modif</th>';
    echo '<th>Suppr</th>';
    echo '</tr>';

    while ($id_salle = $liste_salle->fetch(PDO::FETCH_ASSOC)) {
        echo '<tr>';
        echo '<td>' . $id_salle['id_salle'] . '</td>';
        echo '<td>' . $id_salle['titre'] . '</td>';
        echo '<td>' . $id_salle['categorie'] . '</td>';
        echo '<td>' . substr($id_salle['description'], 0, 14) . '</td>';
        echo '<td><img src="../img/' . $id_salle['photo'] .
            '" class="img-thumbnail" width="140"></td>';
        echo '<td>' . $id_salle['pays'] . '</td>';
        echo '<td>' . $id_salle['ville'] . '</td>';
        echo '<td>' . $id_salle['adresse'] . '</td>';

        echo '<td>' . $id_salle['cp'] . '</td>';
        echo '<td>' . $id_salle['capacite'] . '</td>';

        echo '<td><a href="?action=modifier&id_salle=' . $id_salle['id_salle'] . '" class="btn btn-warning"><i class="fas fa-edit"></i></a></td>';

        echo '<td><a href="?action=supprimer&id_salle=' . $id_salle['id_salle'] . '" class="btn btn-danger" onclick="return(confirm(\'Etes-vous sûr ?\'))"><i class="fas fa-trash-alt"></i></a></td>';

        echo '</tr>';
    }

    echo '</table>';
    echo '</div>';
}
//***************************
// FIN AFFICHAGE DES PRODUITS
//***************************

//***************************
// on affiche le form si l'utilisateur a cliqué sur le bouton "Ajout article"
//  if(isset($_GET['action']) && ($_GET['action'] == 'ajouter' || $_GET['action'] == 'modifier')) {

// }

?>

<div class="starter-template">

    <div class="row">
        <div class="col-12">

            <form method="post" action="" enctype="multipart/form-data">
                <!-- récupération de l'id_article pour la modification -->
                <input type="hidden" name="id_salle" value="<?php echo $id_salle; ?>">
                <div class="row">
                    <div class="col-6">

                        <div class="form-group">
                            <label for="titre">Titre</label>
                            <input type="text" name="titre" id="titre" value="<?php echo $titre; ?>" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" rows="2" class="form-control"><?php echo $description; ?></textarea>
                        </div>
                        <?php
                        // récupération de la photo de l'article en cas de modification. Pour la consever si l'utilisateur n'en charge pas une nouvelle
                        // if(!empty($photo_actuelle)) {
                        // echo '<div class="form-group text-center">
                        // <label>Photo actuelle</label><hr>
                        // <img src="' . URL . '../img/' . $photo_actuelle . '" class="w-25 img-thumbnail" alt="image de l\'article">
                        // <input type="hidden" name="photo_actuelle" value="' . $photo_actuelle . '"></div>';
                        // }

                        ?>

                        <?php if (!empty($photo_actuelle)) : ?>

                            <div class="form-group text-center">
                                <label>Photo actuelle</label>
                                <hr>
                                <img src="../img/<?php echo $photo_actuelle; ?>" class="w-25 img-thumbnail" alt="image de la salle">
                                <input type="hidden" name="photo_actuelle" value="<?php echo $photo_actuelle; ?>">
                            </div>

                        <?php endif; ?>

                        <div class="form-group">
                            <label for="photo">Photo</label>
                            <input type="file" name="photo" id="photo" class="form-control">
                        </div>

                    </div>

                    <div class="col-6">


                        <div class="form-group">

                            <label for="ville">Ville</label>

                            <select name="ville" id="ville" class="form-control">
                                <option <?php if ($ville == 'Paris') {
                                            echo 'selected';
                                        } ?>>Paris</option>
                                <option <?php if ($ville == 'Lyon') {
                                            echo 'selected';
                                        } ?>>Lyon</option>
                                <option <?php if ($ville == 'Marseille') {
                                            echo 'selected';
                                        } ?>>Marseille</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="adresse">adresse</label>
                            <input type="text" name="adresse" id="adresse" value="<?php echo $adresse; ?>" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="cp">Code Postal</label>
                            <input type="text" name="cp" id="cp" value="<?php echo $cp; ?>" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="capacite">Capacite</label>
                            <select name="capacite" id="capacite" class="form-control">

                                <option <?php if ($capacite == '1') {
                                            echo 'selected';
                                        } ?>>1</option>
                                <option <?php if ($capacite == '20') {
                                            echo 'selected';
                                        } ?>>20</option>
                                <option <?php if ($capacite == '50') {
                                            echo 'selected';
                                        } ?>>50</option>
                                <option <?php if ($capacite == '100') {
                                            echo 'selected';
                                        } ?>>100</option>

                            </select>
                        </div>

                        <div class="form-group">
                            <label for="categorie">Catégorie</label>
                            <select name="categorie" id="categorie" class="form-control">
                                <option <?php if ($categorie == 'reunion') {
                                            echo 'selected';
                                        } ?>>reunion</option>
                                <option <?php if ($categorie == 'bureau') {
                                            echo 'selected';
                                        } ?>>bureau</option>
                                <option <?php if ($categorie == 'formation') {
                                            echo 'selected';
                                        } ?>>formation</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="pays">Pays</label>
                            <select name="pays" id="pays" class="form-control">
                                <option <?php if ($pays == 'france') {
                                            echo 'selected';
                                        } ?>>France</option>
                            </select>
                        </div>
                        <div class="form-group col-2">
                            <button type="submit" name="enregistrement" id="enregistrement" class="form-control btn btn-outline-dark"> Enregistrer </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

