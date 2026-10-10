<?php $pageCourante = basename($_SERVER['SCRIPT_NAME']); ?>
<nav class="barre-admin">
  <span class="barre-titre"><i class="fa-solid fa-star"></i> Soirée K</span>
  <div class="barre-liens">
    <a href="dashboard.php" class="<?= $pageCourante === 'dashboard.php' ? 'actif' : '' ?>"><i class="fa-solid fa-gauge"></i><span>Tableau de bord</span></a>
    <a href="prestations.php" class="<?= $pageCourante === 'prestations.php' ? 'actif' : '' ?>"><i class="fa-solid fa-music"></i><span>Prestations</span></a>
    <a href="resultats.php" class="<?= $pageCourante === 'resultats.php' ? 'actif' : '' ?>"><i class="fa-solid fa-ranking-star"></i><span>Résultats</span></a>
    <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i><span>Déconnexion</span></a>
  </div>
</nav>