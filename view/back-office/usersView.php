<?php $title = 'Gestion des membres' ?>


<?php ob_start(); ?>
<?php if (!empty($users_list)) {
    echo '<p>Nombre de membres : <b>' . $users_list->rowCount() . '</b></p>';
} ?>
    <div class="table-responsive">
        <table class="table table-bordered">
            <tr>
                <th>Id membre</th>
                <th>pseudo</th>
                <th>nom</th>
                <th>prenom</th>
                <th>email</th>
                <th>civilite</th>
                <th>statut</th>
                <th>date_enregistrement</th>
                <th>Modif</th>
                <th>Suppr</th>
            </tr>
            <?php
            while ($user = $users_list->fetch(PDO::FETCH_ASSOC)) {
                echo '<tr>';
                echo '<td>' . $user['id_membre'] . '</td>';
                echo '<td>' . $user['pseudo'] . '</td>';
                echo '<td>' . $user['nom'] . '</td>';
                echo '<td>' . $user['prenom'] . '</td>';
                echo '<td>' . $user['email'] . '</td>';
                echo '<td>' . $user['civilite'] . '</td>';
                echo '<td>' . $user['statut'] . '</td>';
                echo '<td>' . $user['date_enregistrement'] . '</td>';

                echo '<td><a href="?action=editUser&user-id=' . $user['id_membre'] . '&#form" class="btn btn-warning"><i class="fas fa-edit"></i></a></td>';
                echo '<td><a href="?action=deleteUser&user-id=' . $user['id_membre'] . '" class="btn btn-danger" onclick="return(confirm(\'Etes-vous sûr ?\'))"><i class="fas fa-trash-alt"></i></a></td>';
                echo '</tr>';
            }
            ?>
        </table>
    </div>
    <!--**************************-->
    <!-- FIN AFFICHAGE DES MEMBRES -->
    <!--**************************-->
<?php
$id_membre = '';
$pseudo = '';
$nom = '';
$prenom = '';
$email = '';
$civilite = '';
$statut = '';

if (!empty($current_user)) {
    $id_membre = $current_user['id_membre'];
    $pseudo = $current_user['pseudo'];
    $nom = $current_user['nom'];
    $prenom = $current_user['prenom'];
    $email = $current_user['email'];
    $civilite = $current_user['civilite'];
    $statut = $current_user['statut'];
}
?>

    <!--******************-->
    <!-- DEBUT FORMULAIRE -->
    <!--******************-->
    <div class="starter-template">
        <div class="row">
            <div class="col-12">
                <form method="post" id="form" action="?action=editUser&amp;user-id=<?= $id_membre ?>">
                    <input type="hidden" name="id_membre" value="<?php echo $id_membre ?>">

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="pseudo">Pseudo</label>
                                <input type="text" name="pseudo" id="pseudo" value="<?php echo $pseudo ?>"
                                       class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="mdp">Mot de passe</label>
                                <input type="password" autocomplete="off" name="mdp" id="mdp" value=""
                                       class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="nom">Nom</label>
                                <input type="text" name="nom" id="nom" value="<?php echo $nom ?>" class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="prenom">Prenom</label>
                                <input type="text" name="prenom" id="prenom" value="<?php echo $nom ?>"
                                       class="form-control">
                            </div>
                        </div>
                        <div class="col-6">

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" name="email" id="email" value="<?php echo $email ?>"
                                       class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="civilite">Civilité</label>
                                <select name="civilite" id="civilite" class="form-control">
                                    <option value="m" <?php if ($civilite == 'm') {
                                        echo 'selected';
                                    } ?>>Homme
                                    </option>
                                    <option value="f" <?php if ($civilite == 'f') {
                                        echo 'selected';
                                    } ?>>Femme
                                    </option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="statut">Statut</label>
                                <select name="statut" id="statut" class="form-control">
                                    <option value="1" <?php if ($statut == 1) {
                                        echo 'selected';
                                    } ?>>Membre
                                    </option>

                                    <option value="2" <?php if ($statut == 2) {
                                        echo 'selected';
                                    } ?>>Administrateur
                                    </option>
                                </select>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="form-control btn btn-outline-dark">Enregistrer</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $content = ob_get_clean(); ?>

<?php
if (!user_is_admin()) {
    $title = 'Accès interdit';
    $content = '<h1>Vers l\'<a href="?action=listProductsIndex">accueil</a></h1>';
}
?>

<?php require('view/template/template.php'); ?>