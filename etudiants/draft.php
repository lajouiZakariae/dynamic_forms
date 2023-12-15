<form action="post.php?action=create" method="post" style="max-width:650px;">
    <div class="row mb-2">
        <div class="col-3"><label for="nom">Nom</label></div>
        <div class="col-9"><input type="text" class="form-control" name="nom" id="nom"></div>
    </div>
    <div class="row mb-2">
        <div class="col-3"><label for="prenom">Prenom</label></div>
        <div class="col-9"><input type="text" class="form-control" name="prenom" id="prenom"></div>
    </div>
    <div class="row mb-2">
        <div class="col-3"><label for="date_inscription">Date inscription</label></div>
        <div class="col-9"><input type="date" class="form-control" name="date_inscription" id="date_inscription"></div>
    </div>
    <div class="row mb-2">
        <div class="col-3"><label for="genre">Genre</label></div>
        <div class="col-9"><select class="form-select" name="genre">
                <option>Choose</option>
                <option value="Male">Male</option>
                <option value="FEMALE">FEMALE</option>
                <option value="PREFER NOT TO SAY">PREFER NOT TO SAY</option>
            </select></div>
    </div>
    <div class="row mb-2">
        <div class="col-3"><label for="adresse">Adresse</label></div>
        <div class="col-9"><input type="text" class="form-control" name="adresse" id="adresse"></div>
    </div>
    <div class="row mb-2">
        <div class="col-3"><label for="id_filiere">Id filiere</label></div>
        <div class="col-9"><input type="text" class="form-control" name="id_filiere" id="id_filiere"></div>
    </div>
    <div class="row mb-2">
        <div class="col-3"><label for="ville">Ville</label></div>
        <div class="col-9"><input type="text" class="form-control" name="ville" id="ville"></div>
    </div>
    <div class="row mb-2">
        <div class="col-3"><label for="message">Message</label></div>
        <div class="col-9"><textarea class="form-control" name="message"></textarea></div>
    </div>
    <div class="row mb-2">
        <div class="col-3"><label for="email">Email</label></div>
        <div class="col-9"><input type="email" class="form-control" name="email" id="email"></div>
    </div>
    <div class="row">
        <div class="col-9 ms-auto"><button type="submit" class="btn btn-primary w-100">Save</button></div>
    </div>
</form>