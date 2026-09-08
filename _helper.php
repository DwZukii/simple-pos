<?php

function dd($data) {
    header('Content-type: application/json');
    echo json_encode($data);
    die();
}

function get($key) {
    if (isset($_GET[$key])) return trim($_GET[$key]);
    return "";
}

function post($key) {
    if (isset($_POST[$key])) {
        return trim($_POST[$key]);
    }
    return "";
}

function redirect($location) {
    header("location: $location");
    die();
}

function flashMessage($name, $message, $type) {

    // remove existing message with the name
    if (isset($_SESSION[FLASH][$name])) {
        unset($_SESSION[FLASH][$name]);
    }

    $_SESSION[FLASH][$name] = ['message' => $message, 'type' => $type];
}

function formattedFlashMessage($flashMessage) {
    return sprintf("<div class='alert alert-%s'>%s</div>",
        $flashMessage['type'],
        $flashMessage['message']
    );
}

function displayFlashMessage($name) {

    if (!isset($_SESSION[FLASH][$name])) return;

    $flashMessage = $_SESSION[FLASH][$name];

    unset($_SESSION[FLASH][$name]);

    echo formattedFlashMessage($flashMessage);
}

// The sales reports let you look at a single day, a month, a year, or
// everything. Rather than writing four sets of queries, the chosen filter is
// turned into a plain start and end date here and every report keeps using the
// range queries it already had.
//
// Returns: type, day, month, year (what the form should show) plus start, end
// and label (what the queries and the heading should use).
function resolveReportFilter()
{
    $type = get('filter_type');

    if (!in_array($type, ['day', 'month', 'year', 'all'], true)) {
        $type = 'day';
    }

    $day   = validPeriod(get('filter_day'), 'Y-m-d', date('Y-m-d'));
    $month = validPeriod(get('filter_month'), 'Y-m', date('Y-m'));
    $year  = validYear(get('filter_year'), date('Y'));

    switch ($type) {
        case 'month':
            $start = $month.'-01';
            $end   = date('Y-m-t', strtotime($start));
            $label = date('F Y', strtotime($start));
            break;

        case 'year':
            $start = $year.'-01-01';
            $end   = $year.'-12-31';
            $label = $year;
            break;

        case 'all':
            // Wide enough to include every row without special casing the
            // queries, which all expect two dates.
            $start = '1000-01-01';
            $end   = '9999-12-31';
            $label = 'All time';
            break;

        default:
            $start = $day;
            $end   = $day;
            $label = date('d M Y', strtotime($day));
            break;
    }

    return [
        'type'  => $type,
        'day'   => $day,
        'month' => $month,
        'year'  => $year,
        'start' => $start,
        'end'   => $end,
        'label' => $label,
    ];
}

// Anything that is not exactly the expected format falls back rather than
// being handed to a query.
function validPeriod($value, $format, $fallback)
{
    $date = DateTime::createFromFormat('!'.$format, $value);

    if ($date && $date->format($format) === $value) {
        return $value;
    }

    return $fallback;
}

function validYear($value, $fallback)
{
    if (strlen($value) === 4 && ctype_digit($value)) {
        return $value;
    }

    return $fallback;
}
