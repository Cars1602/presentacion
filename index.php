<?php
$conexion = mysqli_connect("localhost", "root", "");

if (!$conexion) {
  die("Error de conexion: " . mysqli_connect_error());
}

mysqli_query($conexion, "CREATE DATABASE IF NOT EXISTS BDHOSPITAL");
mysqli_select_db($conexion, "BDHOSPITAL");

mysqli_query($conexion, "CREATE TABLE IF NOT EXISTS PACIENTES (
  CodPaciente INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  Nombre VARCHAR(30) NOT NULL,
  Genero VARCHAR(10) NOT NULL,
  FechaNacimiento DATE NOT NULL
)");

mysqli_query($conexion, "CREATE TABLE IF NOT EXISTS MEDICOS (
  CodMedico INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  Nombre VARCHAR(30) NOT NULL,
  Especialidad VARCHAR(30) NOT NULL,
  Porcentaje INT NOT NULL,
  Cupo INT NOT NULL
)");

mysqli_query($conexion, "CREATE TABLE IF NOT EXISTS CONSULTAS (
  CodConsulta INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  Fecha DATE NOT NULL,
  HoraInicio TIME NOT NULL,
  Diagnostico VARCHAR(40) NOT NULL,
  CodPaciente INT NOT NULL,
  CodMedico INT NOT NULL,
  Cobro INT NOT NULL,
  FOREIGN KEY (CodPaciente) REFERENCES PACIENTES(CodPaciente),
  FOREIGN KEY (CodMedico) REFERENCES MEDICOS(CodMedico)
)");

$cantidadPacientes = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) AS total FROM PACIENTES"));
if ($cantidadPacientes["total"] == 0) {
  mysqli_query($conexion, "INSERT INTO PACIENTES (Nombre, Genero, FechaNacimiento) VALUES
    ('PETER', 'MASCULINO', '2000-01-01'),
    ('JHON', 'MASCULINO', '1990-02-02'),
    ('KATHERINE', 'FEMENINO', '2005-03-03'),
    ('BRIGITTE', 'FEMENINO', '2010-04-04'),
    ('MATT', 'MASCULINO', '2020-05-05')");
}

$cantidadMedicos = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) AS total FROM MEDICOS"));
if ($cantidadMedicos["total"] == 0) {
  mysqli_query($conexion, "INSERT INTO MEDICOS (Nombre, Especialidad, Porcentaje, Cupo) VALUES
    ('Juan Perez', 'Urologo', 10, 20),
    ('Carmen Rojas', 'Pediatra', 15, 15),
    ('Silvia Peralta', 'Cardiologia', 20, 20),
    ('Richard Eguez', 'Pediatra', 30, 30),
    ('Iker Limon', 'Urologo', 40, 40)");
}

$mensaje = "";
$pagina = "registrar";

if (isset($_GET["pagina"])) {
  $pagina = $_GET["pagina"];
}

if (isset($_POST["registrar"])) {
  $fecha = mysqli_real_escape_string($conexion, $_POST["fecha"]);
  $hora = mysqli_real_escape_string($conexion, $_POST["hora"]);
  $diagnostico = mysqli_real_escape_string($conexion, $_POST["diagnostico"]);
  $paciente = (int) $_POST["paciente"];
  $medico = (int) $_POST["medico"];
  $cobro = rand(200, 500);

  $sql = "INSERT INTO CONSULTAS (Fecha, HoraInicio, Diagnostico, CodPaciente, CodMedico, Cobro)
          VALUES ('$fecha', '$hora', '$diagnostico', $paciente, $medico, $cobro)";

  if (mysqli_query($conexion, $sql)) {
    $mensaje = "Consulta registrada correctamente. Cobro: $cobro Bs.";
  } else {
    $mensaje = "No se pudo registrar la consulta.";
  }
}

$pacientes = mysqli_query($conexion, "SELECT * FROM PACIENTES ORDER BY Nombre");
$medicos = mysqli_query($conexion, "SELECT * FROM MEDICOS ORDER BY Nombre");

$fechaBuscada = "";
$consultas = false;

if (isset($_GET["buscar"])) {
  $fechaBuscada = mysqli_real_escape_string($conexion, $_GET["fecha_buscar"]);
  $consultas = mysqli_query($conexion, "SELECT C.CodConsulta, C.Fecha, C.HoraInicio, C.Diagnostico, C.Cobro,
    P.Nombre AS Paciente, M.Nombre AS Medico, M.Especialidad
    FROM CONSULTAS C
    INNER JOIN PACIENTES P ON C.CodPaciente = P.CodPaciente
    INNER JOIN MEDICOS M ON C.CodMedico = M.CodMedico
    WHERE C.Fecha = '$fechaBuscada'
    ORDER BY C.HoraInicio ASC");
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>BD Hospital</title>
  <link rel="stylesheet" href="estilos/estilos.css">
</head>
<body>
  <header>
    <h1>GESTION DE CONSULTAS MEDICAS</h1>
  </header>

  <nav id="menu">
    <ul>
      <li><a href="index.php?pagina=registrar">Registrar Consulta</a></li>
      <li><a href="index.php?pagina=informacion">Informacion</a></li>
    </ul>
  </nav>

  <div id="contenedor">
    <section>
      <?php if ($pagina == "registrar") { ?>
      <article>
        <h2>Registrar consulta medica</h2>

        <?php if ($mensaje != "") { ?>
          <p class="mensaje"><?php echo $mensaje; ?></p>
        <?php } ?>

        <form method="POST">
          <label>Fecha:</label>
          <input type="date" name="fecha" required>

          <label>Hora de inicio:</label>
          <input type="time" name="hora" required>

          <label>Diagnostico:</label>
          <input type="text" name="diagnostico" maxlength="40" required>

          <label>Paciente:</label>
          <select name="paciente" required>
            <?php while ($fila = mysqli_fetch_assoc($pacientes)) { ?>
              <option value="<?php echo $fila["CodPaciente"]; ?>">
                <?php echo $fila["Nombre"] . " - " . $fila["Genero"]; ?>
              </option>
            <?php } ?>
          </select>

          <label>Medico:</label>
          <select name="medico" required>
            <?php while ($fila = mysqli_fetch_assoc($medicos)) { ?>
              <option value="<?php echo $fila["CodMedico"]; ?>">
                <?php echo $fila["Nombre"] . " - " . $fila["Especialidad"]; ?>
              </option>
            <?php } ?>
          </select>

          <button type="submit" name="registrar">Registrar</button>
        </form>
      </article>
      <?php } ?>

      <?php if ($pagina == "informacion") { ?>
      <article>
        <h2>Informacion de consultas por fecha</h2>

        <form method="GET">
          <input type="hidden" name="pagina" value="informacion">
          <label>Elegir fecha:</label>
          <input type="date" name="fecha_buscar" value="<?php echo $fechaBuscada; ?>" required>
          <button type="submit" name="buscar">Buscar</button>
        </form>

        <?php if ($consultas != false) { ?>
          <table>
            <tr>
              <th>Codigo</th>
              <th>Fecha</th>
              <th>Hora</th>
              <th>Paciente</th>
              <th>Medico</th>
              <th>Especialidad</th>
              <th>Diagnostico</th>
              <th>Cobro</th>
            </tr>

            <?php if (mysqli_num_rows($consultas) == 0) { ?>
              <tr>
                <td colspan="8">No hay consultas en esta fecha.</td>
              </tr>
            <?php } ?>

            <?php while ($fila = mysqli_fetch_assoc($consultas)) { ?>
              <tr>
                <td><?php echo $fila["CodConsulta"]; ?></td>
                <td><?php echo $fila["Fecha"]; ?></td>
                <td><?php echo $fila["HoraInicio"]; ?></td>
                <td><?php echo $fila["Paciente"]; ?></td>
                <td><?php echo $fila["Medico"]; ?></td>
                <td><?php echo $fila["Especialidad"]; ?></td>
                <td><?php echo $fila["Diagnostico"]; ?></td>
                <td><?php echo $fila["Cobro"]; ?> Bs.</td>
              </tr>
            <?php } ?>
          </table>
        <?php } ?>
      </article>
      <?php } ?>
    </section>

    <aside>
      <h2>Investigacion</h2>
      <p>Se uso PHP con MySQLi para guardar datos en MySQL.</p>
      <p>El cobro se calcula al azar con rand(200, 500).</p>
      <p>Las consultas se muestran por fecha y ordenadas por hora.</p>
    </aside>
  </div>

  <footer>
    <h2>VALORACION 02 - 2026</h2>
  </footer>
</body>
</html>
