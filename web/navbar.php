<?php
$nombre = $_COOKIE['nombre_profesor'] ?? 'Profesor';
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
  <div class="container-fluid">
    <!-- Botón hamburguesa -->
    <button class="btn btn-outline-secondary me-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu">
      <i class="bi bi-list"></i>
    </button>
    <!-- Marca con enlace -->
    <a class="navbar-brand" href="dashIES.php">FCT - IES Emili Darder</a>
    <!-- Usuario -->
    <div class="ms-auto">
      <div class="dropdown">
        <button class="btn btn-outline-primary dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
          <i class="bi bi-person-badge"></i> <?= htmlspecialchars($nombre) ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="logout.php">Salir</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>

<!-- MENÚ LATERAL -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasMenu">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">Menú</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body p-0">
    <ul class="list-group list-group-flush">
      <li class="list-group-item bg-light">
        <a href="alumnes.php" class="text-decoration-none text-primary d-flex align-items-center gap-2">
          <i class="bi bi-people-fill"></i> Alumnos
        </a>
      </li>
      <li class="list-group-item">
        <a href="empreses.php" class="text-decoration-none text-primary d-flex align-items-center gap-2">
          <i class="bi bi-building"></i> Empresas
        </a>
      </li>
      <li class="list-group-item bg-light">
        <a href="professors.php" class="text-decoration-none text-primary d-flex align-items-center gap-2">
          <i class="bi bi-person-badge"></i> Profesores
        </a>
      </li>
      <li class="list-group-item">
        <a href="solicituds.php" class="text-decoration-none text-primary d-flex align-items-center gap-2">
          <i class="bi bi-inbox-fill"></i> Solicitudes
        </a>
      </li>
    </ul>
  </div>
</div>
