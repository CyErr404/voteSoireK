document.addEventListener('DOMContentLoaded', function () {
  var liste = document.getElementById('liste');
  if (!liste) return;

  var cartes = Array.prototype.slice.call(liste.querySelectorAll('.prestation'));
  var recherche = document.getElementById('recherche');
  var aucun = document.getElementById('aucun');
  var message = document.getElementById('message');
  var btnValider = document.getElementById('btn-valider');
  var resume = document.querySelectorAll('.vote-resume li');
  var modal = document.getElementById('modal');
  var modalListe = document.getElementById('modal-liste');
  var modalErreur = document.getElementById('modal-erreur');
  var btnModifier = document.getElementById('btn-modifier');
  var btnConfirmer = document.getElementById('btn-confirmer');
  var merci = document.getElementById('merci');
  var confettis = document.getElementById('confettis');

  var choix = []; // [{id, nom}, ...] dans l'ordre : 1er, 2e, 3e
  var minuteur = null;

  function position(id) {
    for (var i = 0; i < choix.length; i++) {
      if (choix[i].id === id) return i;
    }
    return -1;
  }

  function afficherMessage(texte) {
    message.textContent = texte;
    message.hidden = false;
    clearTimeout(minuteur);
    minuteur = setTimeout(function () { message.hidden = true; }, 2800);
  }

  function majAffichage() {
    cartes.forEach(function (c) {
      var i = position(c.dataset.id);
      var badge = c.querySelector('.prestation-badge');
      if (i === -1) {
        c.classList.remove('choisie');
        badge.textContent = '';
      } else {
        c.classList.add('choisie');
        badge.textContent = i + 1;
      }
    });

    resume.forEach(function (li, i) {
      var nom = li.querySelector('.resume-nom');
      if (choix[i]) {
        nom.textContent = choix[i].nom;
        li.classList.add('rempli');
      } else {
        nom.textContent = '—';
        li.classList.remove('rempli');
      }
    });

    btnValider.disabled = choix.length !== 3;
  }

  cartes.forEach(function (carte) {
    carte.addEventListener('click', function () {
      var id = carte.dataset.id;
      var i = position(id);

      if (i !== -1) {
        choix.splice(i, 1);
      } else if (choix.length >= 3) {
        afficherMessage('Tu as déjà 3 choix. Retire-en un pour en changer.');
        return;
      } else {
        choix.push({ id: id, nom: carte.dataset.nom });
        if (navigator.vibrate) navigator.vibrate(15);
      }

      message.hidden = true;
      majAffichage();
    });
  });

  // Recherche (ignore les accents et les majuscules)
  function normaliser(t) {
    return t.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  }

  recherche.addEventListener('input', function () {
    var terme = normaliser(recherche.value.trim());
    var visibles = 0;
    cartes.forEach(function (c) {
      var ok = normaliser(c.dataset.nom).indexOf(terme) !== -1;
      c.hidden = !ok;
      if (ok) visibles++;
    });
    aucun.hidden = visibles !== 0;
  });

  // Fenêtre de confirmation
  function ouvrirModal() {
    modalListe.innerHTML = '';
    choix.forEach(function (c, i) {
      var li = document.createElement('li');
      var rang = document.createElement('span');
      rang.className = 'rang';
      rang.textContent = i + 1;
      var nom = document.createElement('span');
      nom.textContent = c.nom;
      li.appendChild(rang);
      li.appendChild(nom);
      modalListe.appendChild(li);
    });
    modalErreur.hidden = true;
    modal.hidden = false;
  }

  function fermerModal() {
    modal.hidden = true;
  }

  function reactiverBoutons() {
    btnConfirmer.disabled = false;
    btnModifier.disabled = false;
    btnConfirmer.textContent = 'Confirmer';
  }

  btnValider.addEventListener('click', function () {
    if (choix.length === 3) ouvrirModal();
  });
  btnModifier.addEventListener('click', fermerModal);
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !modal.hidden) fermerModal();
  });

  // Envoi du vote
  btnConfirmer.addEventListener('click', function () {
    modalErreur.hidden = true;
    btnConfirmer.disabled = true;
    btnModifier.disabled = true;
    btnConfirmer.textContent = 'Envoi en cours...';

    fetch('submit_vote.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        choix1: choix[0].id,
        choix2: choix[1].id,
        choix3: choix[2].id
      })
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.succes) {
          fermerModal();
          afficherMerci();
        } else {
          modalErreur.textContent = data.message || 'Une erreur est survenue.';
          modalErreur.hidden = false;
          reactiverBoutons();
        }
      })
      .catch(function () {
        modalErreur.textContent = 'Problème de connexion. Réessaie.';
        modalErreur.hidden = false;
        reactiverBoutons();
      });
  });

  // Animation de fin
  function afficherMerci() {
    merci.hidden = false;
    lancerConfettis();
  }

  function lancerConfettis() {
    var couleurs = ['#ff2d95', '#19a9ff', '#ffffff', '#ffb3d9', '#a8defc'];
    for (var i = 0; i < 70; i++) {
      var p = document.createElement('i');
      p.className = 'confetti';
      p.style.left = (Math.random() * 100) + 'vw';
      p.style.background = couleurs[Math.floor(Math.random() * couleurs.length)];
      p.style.animationDuration = (2.5 + Math.random() * 2.5) + 's';
      p.style.animationDelay = (Math.random() * 1.2) + 's';
      confettis.appendChild(p);
    }
    setTimeout(function () { confettis.innerHTML = ''; }, 7000);
  }
});