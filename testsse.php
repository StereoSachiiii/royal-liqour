<!DOCTYPE html>
<html>
<head>
    <title>SSE Demo</title>
</head>
<body>
    <h1>Server-Sent Events Demo</h1>
    <div id="clock"></div>

    <script>
        // 1️⃣ Connect to the SSE endpoint
        const evtSource = new EventSource('admin/events/EventDispatcher.php');

        // 2️⃣ Listen for messages
        evtSource.onmessage = function(event) {
            document.getElementById('clock').innerText = event.data;
        };

        // 3️⃣ Handle errors (optional)
        evtSource.onerror = function() {
            console.error("SSE connection lost. Trying to reconnect...");
        };
    </script>
</body>
</html>
