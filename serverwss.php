<?php require __DIR__.'/vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Illuminate\Support\Facades\Auth;

define('APP_PORT', 8088);

class ServerImpl implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        //Auth::user();
        //$this->middleware('auth');
        $this->clients = new \SplObjectStorage;
    }

    
    public function onOpen(ConnectionInterface $conn) {
        $dbLink = mysqli_connect('mysql-dams.alwaysdata.net', 'dams', 'lemdpbasetest')
        or die('Erreur de connexion au serveur : ' . mysqli_connect_error());

        mysqli_select_db($dbLink, 'dams_lumen')
        or die('Erreur dans la sélection de la base : ' . mysqli_error($dbLink));
        
        $this->clients->attach($conn);
        $row = "1";
        
        $verification = "SELECT Message FROM Message WHERE IdMessage = $row"; //Vérifie les données rentrées
        if ($resultatverification = mysqli_query($dbLink, $verification)) {
            $row = mysqli_fetch_assoc($resultatverification);
        }
        $arr = array('chaine' => $row['Message'],
        'choix' => 1, 'pos' => 1, 'mode' => 1, 'fin' => 6);
        $msg = json_encode($arr);
        $conn->send($msg);
        echo "New connection! ({$conn->resourceId}).\n";
    }

    public function onMessage(ConnectionInterface $conn, $msg) {
        echo sprintf("New message from '%s': %s\n\n\n", $conn->resourceId, $msg);
        foreach ($this->clients as $client) { // BROADCAST
            $message = json_decode($msg, true);
            if ($conn !== $client) {
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} is gone.\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error occured on connection {$conn->resourceId}: {$e->getMessage()}\n\n\n";
        $conn->close();
    }
}

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new ServerImpl()
        )
    ),
    APP_PORT
);
echo "Server created on port " . APP_PORT . "\n\n";
$server->run();