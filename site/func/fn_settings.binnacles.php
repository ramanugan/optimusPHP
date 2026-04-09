<?
session_start();
include('../init.php');
include('fn_common.php');
checkUserSession();

loadLanguage($_SESSION["language"], $_SESSION["units"]);

// check privileges
if ($_SESSION["privileges"] == 'subuser') {
	$user_id = $_SESSION["manager_id"];
} else {
	$user_id = $_SESSION["user_id"];
}

if (@$_GET['cmd'] == 'load_rows_binnacles_list') {
    $page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit  = isset($_GET['rows']) ? (int)$_GET['rows'] : 50;
    $sidx   = isset($_GET['sidx']) && $_GET['sidx'] !== '' ? $_GET['sidx'] : 'b.event_date';
    $sord   = (isset($_GET['sord']) && in_array(strtoupper($_GET['sord']), ['ASC','DESC'])) ? $_GET['sord'] : 'DESC';
    $search = isset($_GET['s']) ? trim($_GET['s']) : '';

    if ($limit <= 0) {
        $limit = 50;
    }
    if ($page <= 0) {
        $page = 1;
    }

    $searchEsc = mysqli_real_escape_string($ms, $search);

    if ($_SESSION["cpanel_privileges"] == 'super_admin') {

        $where = [];
        $where[] = "(u.privileges LIKE '%user%' OR u.privileges LIKE '%admin%')";

        if ($search !== '') {
            $where[] = "(u.username LIKE '%{$searchEsc}%' OR b.event LIKE '%{$searchEsc}%')";
        }

        $where[] = "b.event_date > DATE_SUB(NOW(), INTERVAL 3 MONTH)";

        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $qCount = "
            SELECT COUNT(*) AS cnt
            FROM gs_binnacle AS b
            INNER JOIN gs_users AS u ON b.id_user = u.id
            $whereSql
        ";

        $rCount = mysqli_query($ms, $qCount);
        $rowCount = mysqli_fetch_assoc($rCount);
        $count = (int)$rowCount['cnt'];

        $total_pages = ($count > 0) ? ceil($count / $limit) : 1;
        if ($page > $total_pages) {
            $page = $total_pages;
        }
        $start = $limit * ($page - 1);
        if ($start < 0) $start = 0;

        $qData = "
            SELECT b.*, u.username AS username
            FROM gs_binnacle AS b
            INNER JOIN gs_users AS u ON b.id_user = u.id
            $whereSql
            ORDER BY b.event_date DESC
            LIMIT $start, $limit
        ";

        $r = mysqli_query($ms, $qData);

        $response = new stdClass();
        $response->page    = $page;
        $response->total   = $total_pages;
        $response->records = $count;
        $response->rows    = [];

        if ($r) {
            $i = 0;
            while ($row = mysqli_fetch_assoc($r)) {
                $binnacles_id     = $row["id"];
                $binnacles_nombre = $row["username"];
                $binnacles_evento = $row["event"];
                $binnacles_fecha  = $row["event_date"];
                $binnacles_fecha  = convUserIDTimezone($user_id, $binnacles_fecha);

                $response->rows[$i]['id']   = $binnacles_id;
                $response->rows[$i]['cell'] = array($binnacles_nombre, $binnacles_evento, $binnacles_fecha);
                $i++;
            }
        }

        header('Content-type: application/json; charset=utf-8');
        echo json_encode($response);
        die;

    } else {

        $baseWhere = [];
        $baseWhere[] = "(id_user IN (SELECT id FROM gs_users WHERE manager_id='" . mysqli_real_escape_string($ms, $_SESSION['user_id']) . "')
                         OR id_user='" . mysqli_real_escape_string($ms, $_SESSION['user_id']) . "')";

        if ($search !== '') {
            $baseWhere[] = "(SELECT username FROM gs_users WHERE id = id_user) LIKE '%{$searchEsc}%'";
        }

        $baseWhere[] = "event_date > DATE_SUB(NOW(), INTERVAL 3 MONTH)";

        $whereSql = 'WHERE ' . implode(' AND ', $baseWhere);

        $qCount = "
            SELECT COUNT(*) AS cnt
            FROM gs_binnacle
            $whereSql
        ";

        $rCount = mysqli_query($ms, $qCount);
        $rowCount = mysqli_fetch_assoc($rCount);
        $count = (int)$rowCount['cnt'];

        $total_pages = ($count > 0) ? ceil($count / $limit) : 1;
        if ($page > $total_pages) {
            $page = $total_pages;
        }
        $start = $limit * ($page - 1);
        if ($start < 0) $start = 0;

        $qData = "
            SELECT id,
                   (SELECT username FROM gs_users WHERE id = id_user) AS nombre,
                   event  AS evento,
                   event_date AS fecha
            FROM gs_binnacle
            $whereSql
            ORDER BY event_date DESC, $sidx $sord
            LIMIT $start, $limit
        ";

        $r = mysqli_query($ms, $qData);

        $response = new stdClass();
        $response->page    = $page;
        $response->total   = $total_pages;
        $response->records = $count;
        $response->rows    = [];

        if ($r) {
            $i = 0;
            while ($row = mysqli_fetch_assoc($r)) {
                $binnacles_id     = $row["id"];
                $binnacles_nombre = $row["nombre"];
                $binnacles_evento = $row["evento"];
                $binnacles_fecha  = $row["fecha"];

                $response->rows[$i]['id']   = $binnacles_id;
                $response->rows[$i]['cell'] = array($binnacles_nombre, $binnacles_evento, $binnacles_fecha);
                $i++;
            }
        }

        header('Content-type: application/json; charset=utf-8');
        echo json_encode($response);
        die;
    }
}
