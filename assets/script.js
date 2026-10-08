document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('form-vote');
  if (!form) return;

  var selects = [
    document.getElementById('choix1'),
    document.getElementById('choix2'),
    document.getElementById('choix3')
  ];
  var erreurEl = document.getElementById('erreur');

  function afficherErreur(message) {
    erreurEl.textContent = message;
    erreurEl.hidden = false;
  }

  function masquerErreur() {
    erreurEl.hidden = true;
  }

  form.addEventListener('submit', function (evt) {
    evt.preventDefault();
    masquerErreur();

    var valeurs = selects.map(function (s) { return s.value; });

    if (valeurs.some(function (v) { return v === ''; })) {
      afficherErreur('Veuillez remplir vos 3 choix.');
      return;
    }

    if (new Set(valeurs).size !== 3) {
      afficherErreur('Vos 3 choix doivent être différents.');
      return;
    }

    var bouton = form.querySelector('button[type="submit"]');
    bouton.disabled = true;
    bouton.textContent = 'Envoi en cours...';

    fetch('submit_vote.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ choix1: valeurs[0], choix2: valeurs[1], choix3: valeurs[2] })
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.succes) {
          document.querySelector('main.page').innerHTML =
            '<h1>Merci !</h1><div class="carte message succes"><p>Votre vote a bien été enregistré.</p></div>';
        } else {
          afficherErreur(data.message || 'Une erreur est survenue.');
          bouton.disabled = false;
          bouton.textContent = 'Valider mon vote';
        }
      })
      .catch(function () {
        afficherErreur('Problème de connexion. Réessayez.');
        bouton.disabled = false;
        bouton.textContent = 'Valider mon vote';
      });
  });
});
