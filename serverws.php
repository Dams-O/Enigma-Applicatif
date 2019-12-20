<?php require __DIR__ . '/vendor/autoload.php';

use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\MessageComponentInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

define('APP_PORT', 8088);

class ServerImpl implements MessageComponentInterface
{
    protected $clients;

    /*
     * Le constructeur du serveur
     */
    public function __construct()
    {
        //Auth::user();
        //$this->middleware('auth');
        $this->clients = new \SplObjectStorage;
    }
    /**
     * WebSocket - Fonction OnOpen
     *
     * Connexion à la base de données pour pouvoir effectuer différentes requêtes
     * Le serveur se connecte au client, puis envoi les informations au même client afin de tester différentes clés
     * @param ConnectionInterface $conn : Connexion à l'interface
     * @return Un message disant qu'un utilisateur est connecté
     */
    public function onOpen(ConnectionInterface $conn)
    {
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
        //Ici le changement des valeurs modifie la façon de décrypter
        //l'idée serait de multiplié les différentes possibilités en envoyant
        //des valeurs différentes à plusieurs client
        $arr = array('chaine' => $row['Message'],
            'choix' => 1, 'pos' => 1, 'mode' => 1, 'fin' => 6);
        $msg = json_encode($arr);
        $conn->send($msg);
        echo "New connection! ({$conn->resourceId}).\n";
    }

    /**
     * WebSocket - Fonction OmMessage
     *
     * Message qui sera envoyé pour confirmer la relation client - serveur
     * @param ConnectionInterface $conn : Connexion à l'interface
     * @param string $msg : Message à envoyer
     */
    public function onMessage(ConnectionInterface $conn, $msg)
    {
        echo sprintf("New message from '%s': %s\n\n\n", $conn->resourceId, $msg);
        foreach ($this->clients as $client) { // BROADCAST
            $message = json_decode($msg, true);
            if ($conn !== $client) {
                $client->send($msg);
            }
        }
    }

    /**
     * WebSocket - Fonction onClose
     *
     * Permet de déconnecter la relation client serveur existante
     * @param ConnectionInterface $conn : Connexion à l'interface
     */
    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} is gone.\n";
    }

    /**
     * WebSocket - Fonction onError
     *
     * Permet de gérer les erreurs existantes
     * @param ConnectionInterface $conn : Connexion à l'interface
     * @param Exception $e : Exception
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
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
