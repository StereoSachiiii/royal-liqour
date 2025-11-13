<?php
// 1️⃣ Tell the browser this is an SSE stream
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache'); // prevent caching
header('Connection: keep-alive'); // keep connection open

// 2️⃣ Optional: disable output buffering
while (ob_get_level() > 0) {
    ob_end_flush();
}
ob_implicit_flush(true);

// 3️⃣ Loop forever, sending events every second
while (true) {
    $time = date('H:i:s');

    // 4️⃣ Send the event
    echo "data: The time is $time\n\n";

    // 5️⃣ Flush to make sure browser receives it immediately
    flush();

    // 6️⃣ Wait 1 second before sending the next event
    sleep(1);
}
