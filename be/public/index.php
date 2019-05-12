<?php
header("Access-Control-Allow-Origin: *");

$db = new PDO('mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_NAME'), getenv('DB_USER'), getenv('DB_PASSWORD'));
$path_only = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path_only === '/') {
  if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $job_data = [];
    $query = $db->query('SELECT * FROM jobs');
    if ($query) {
      $job_data = $query->fetchAll();
    }
    $jobs = [];

    foreach ($job_data as $job) {
      $jobs[] = [
        'id' => $job['id'],
        'position' => $job['position'],
        'company_name' => $job['company_name'],
        'location' => $job['location'],
        'apply' => $job['apply'],
        'remote' => $job['remote'],
        'additional' => $job['additional'],
      ];
    }

    echo json_encode($jobs);
    return;
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = filter_data($_REQUEST);

    if (contains_data($data) === TRUE) {
      $query = $db->prepare('INSERT INTO jobs (id, position, company_name, location, apply, remote, additional) VALUES (:id,:pos,:company,:location,:apply,:remote,:additional)');
      $id = implode('-', [
          str_replace(' ', '-', $data['position']),
          str_replace(' ', '-', $data['company_name']),
        ]) . '-' . random_int(10000, 99999);
      $query->bindParam(':id', $id);
      $query->bindParam(':pos', $data['position']);
      $query->bindParam(':company', $data['company_name']);
      $query->bindParam(':location', $data['location']);
      $query->bindParam(':apply', $data['apply']);
      $query->bindParam(':remote', $data['remote']);
      $query->bindParam(':additional', $data['additional']);
      $query->execute();
      if ($query->errorCode()) {
        echo json_encode([
          'status' => 'error',
          'message' => $query->errorCode(),
        ]);
        return;
      }

      echo json_encode(['status' => 'success', 'message' => $id]);
      return;
    }
  }
  echo json_encode(['status' => 'error']);
}

if ($path_only !== '/') {
  $query = $db->prepare('SELECT * FROM jobs WHERE id = ?');
  $split = explode('/', $path_only);
  $id = $split[1];
  if ($query) {
    $query->execute([$id]);
    $job_data = $query->fetch();
  }

  $job = [
    'id' => $job_data['id'],
    'position' => $job_data['position'],
    'company_name' => $job_data['company_name'],
    'location' => $job_data['location'],
    'apply' => $job_data['apply'],
    'remote' => $job_data['remote'],
    'additional' => $job_data['additional'],
  ];

  echo json_encode($job);
  return;
}

/**
 * Filters incoming data.
 *
 * @param array $dirty
 *   Dirty array to sanitize.
 *
 * @return array
 *   Cleaned up array.
 */
function filter_data(array $dirty): array {
  $clean = [];
  foreach ($dirty as $key => $item) {
    $clean[$key] = strip_tags($item);
  }

  return $clean;
}

/**
 * Spot check incoming data for required fields.
 *
 * @param array $array
 *   Incoming data array.
 *
 * @return bool
 *   TRUE is data contains proper data, FALSE otherwise.
 */
function contains_data(array $array): bool {
  $required_data = [
    'position',
    'company_name',
    'location',
    'apply',
    'remote',
    'additional',
  ];
  foreach ($required_data as $datum) {
    if (!isset($array[$datum])) {
      return $datum;
    }
  }

  return TRUE;
}