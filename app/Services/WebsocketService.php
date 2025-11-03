<?php

namespace App\Services;

class WebsocketService
{
    public function broadcastMessage($channel, $type, $data)
    {
        $host   = env('WEBSOCKET_BASE_URL');
        $port   = env('WEBSOCKET_PORT');
        $path   = "/$channel";
        $useTls = false;
        $msg    = json_encode([
            'type'    => $type,
            'data'    => $data,
        ]);

        $remote = ($useTls ? 'ssl' : 'tcp') . "://{$host}:{$port}";
        $ctx = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'SNI_enabled' => true,
                'SNI_server_name' => $host,
            ],
        ]);

        $fp = @stream_socket_client($remote, $errno, $errstr, 5, STREAM_CLIENT_CONNECT, $ctx);
        if (!$fp) { die("Connect failed: $errstr ($errno)\n"); }
        stream_set_timeout($fp, 5);

        // Handshake
        $key = base64_encode(random_bytes(16));
        $headers =
        "GET {$path} HTTP/1.1\r\n" .
        "Host: {$host}:{$port}\r\n" .
        "Upgrade: websocket\r\n" .
        "Connection: Upgrade\r\n" .
        "Sec-WebSocket-Key: {$key}\r\n" .
        "Sec-WebSocket-Version: 13\r\n\r\n";

        fwrite($fp, $headers);
        $resp = stream_get_line($fp, 8192, "\r\n\r\n");
        if ($resp === false || strpos($resp, " 101 ") === false) {
            fclose($fp);
            die("Handshake failed:\n$resp\n");
        }

        // Frame a single TEXT message (masked)
        $payload = $msg;
        $len     = strlen($payload);

        $frame = chr(0x81);
        if ($len <= 125) {
            $frame .= chr(0x80 | $len);
        } elseif ($len < 65536) {
            $frame .= chr(0x80 | 126) . pack('n', $len);
        } else {
            $frame .= chr(0x80 | 127) . pack('J', $len);
        }
        $mask = random_bytes(4);
        $masked = '';
        for ($i = 0; $i < $len; $i++) {
            $masked .= $payload[$i] ^ $mask[$i % 4];
        }
        fwrite($fp, $frame . $mask . $masked);

        // Optional: send a CLOSE frame
        fwrite($fp, chr(0x88) . chr(0x80) . random_bytes(2));

        fclose($fp);
        echo "Sent.\n";
    }
}