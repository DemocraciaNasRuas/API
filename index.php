<?php

require 'vendor/autoload.php';

use Respect\Rest\Router;
use Respect\Config\Container;
use Respect\Validation\Validator as v;
use Respect\Relational\Mapper;
use Respect\Data\Collections\Collection;

/** 
 * Ler arquivo de configuração
 */
$config = new Container('config.ini');

/** 
 * Criar instância PDO com o SQLite usando as configs
 */
// diretório precisa ter permissão de escrita também

// $mapper = new Mapper(new PDO($config->dsn));

$mapper = new Mapper(new PDO( "mysql:host=localhost;dbname=democratic_streets", "root", "" ) );

// Criar instância do router
$router = new Router();
 
/** 
 * Rota para qualquer tipo de request (any)
 */
$router->any('/', function () 
{
    return 'Democracia nas ruas!';
});

$router->get('/protests/*', function ($data) use ($mapper) 
{
    $data = $_GET;

    // Validar com negação se string esta preenchida
    if ( !isset( $data ) || count( $data ) == 0 ) 
    {
        $protests = $mapper->protests->fetchAll();
        
        foreach ( $protests as $protest ) 
        {
            $organizer_protest = $mapper->organizer_protest( array( 'protests_id' => $protest->id ) )->fetch();

            $protest_organizer = array( 'protests' => $protest, 'organizer' => $organizer_protest );

            $return[] = $protest_organizer;
        }

        header('HTTP/1.1 200 Ok');
        return json_encode( $return );
    }

    $params_search = array();

    // tratar os dados
    $protests_search = new stdClass();
    $protests_search->keywords = isset( $data['keywords'] ) ? filter_var( $data['keywords'], FILTER_SANITIZE_FULL_SPECIAL_CHARS ) : '';
    $protests_search->city = isset( $data['city'] ) ? filter_var( $data['city'], FILTER_SANITIZE_FULL_SPECIAL_CHARS ) : '';
    $protests_search->state = isset( $data['state'] ) ? filter_var( $data['state'], FILTER_SANITIZE_FULL_SPECIAL_CHARS ) : '';
    $protests_search->neighborhood = isset( $data['neighborhood'] ) ? filter_var( $data['neighborhood'], FILTER_SANITIZE_FULL_SPECIAL_CHARS ) : '';
    $protests_search->date = isset( $data['date'] ) ? date('Y-m-d H:i:s', strtotime( $data['date'] ) ) : '';

    if( $protests_search->city ) $params_search['city'] = $protests_search->city;
    if( $protests_search->state ) $params_search['state'] = $protests_search->state;
    if( $protests_search->date ) $params_search['date >='] = date('Y-m-d H:i:s', strtotime($protests_search->date));
    if( $protests_search->neighborhood ) $params_search['neighborhood LIKE'] = "%" . $protests_search->neighborhood . "%";

    $protesto = $mapper->organizer_protest->protests( $params_search )->fetchAll();

    if ( !$protesto ) 
    {
        header('HTTP/1.1 404 Not Found');
    
        return 'Não encontrado'; 
    }

    return $protesto;

});


$router->post('/protest', function () use ($mapper) 
{
    $_POST = json_decode( file_get_contents('php://input'), 1 );

    //pega os dados via $_POST
    if ( !isset($_POST) || !isset($_POST['organizer_protest']) || v::not(v::arr())->validate($_POST['organizer_protest']) || !isset($_POST['protest']) || v::not(v::arr())->validate($_POST['protest']) ) 
    {
        header('HTTP/1.1 400 Bad Request');
    
        return 'Faltam parâmetros'; 
    }

    // Validar os dados de protesto
    $validationProtest = v::arr()                                           // verifica se é um array                
                         ->key('title', $rule = v::string()->notEmpty())    // verifica se a key 'title' está vazia   
                         ->key('description', $rule)                        // verifica se a key 'description' está vazia 
                         ->key('date', $rule)                               // verifica se a key 'date' está vazia 
                         ->key('state', $rule)                              // verifica se a key 'state' está vazia 
                         ->key('street', $rule)                             // verifica se a key 'street' está vazia 
                         ->key('postal_code', $rule)                        // verifica se a key 'postal_code' está vazia 
                         ->key('city', $rule)                               // verifica se a key 'city' está vazia 
                         ->key('url', $rule)                                // verifica se a key 'url' está vazia 
                         ->validate($_POST['protest']);

    // Validar os dados de organizador
    $validationOrganizer = v::arr()                                         // verifica se é um array                
                         ->key('title', $rule)                              // verifica se a key 'title' está vazia   
                         ->key('description', $rule)                        // verifica se a key 'description' está vazia 
                         ->validate($_POST['organizer_protest']);

    if ( !$validationProtest || !$validationOrganizer ) 
    {
        header('HTTP/1.1 400 Bad Request');
    
        return 'Faltam parâmetros'; 
    }

    // tratar os dados
    $protest         = new stdClass();
    $protest->title   = filter_var( $_POST['protest']['title'],   FILTER_SANITIZE_FULL_SPECIAL_CHARS );
    $protest->description = filter_var( $_POST['protest']['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS );
    $protest->date = date('Y-m-d H:i:s', strtotime( $_POST['protest']['date'] ) );
    $protest->street = filter_var( $_POST['protest']['street'], FILTER_SANITIZE_FULL_SPECIAL_CHARS );
    $protest->number = filter_var( $_POST['protest']['number'], FILTER_SANITIZE_FULL_SPECIAL_CHARS );
    $protest->neighborhood = filter_var( $_POST['protest']['neighborhood'], FILTER_SANITIZE_FULL_SPECIAL_CHARS );
    $protest->postal_code = filter_var( $_POST['protest']['postal_code'], FILTER_SANITIZE_FULL_SPECIAL_CHARS );
    $protest->complement = filter_var( $_POST['protest']['complement'], FILTER_SANITIZE_FULL_SPECIAL_CHARS );
    $protest->reference = filter_var( $_POST['protest']['reference'], FILTER_SANITIZE_FULL_SPECIAL_CHARS );
    $protest->city = filter_var( $_POST['protest']['city'], FILTER_SANITIZE_FULL_SPECIAL_CHARS );
    $protest->state = filter_var( $_POST['protest']['state'], FILTER_SANITIZE_FULL_SPECIAL_CHARS );
    $protest->url = filter_var( $_POST['protest']['url'], FILTER_SANITIZE_URL );
    $protest->image = $_POST['protest']['image'];
    $protest->created_at = date( "Y-m-d H:i:s" );
    $protest->updated_at = date( "Y-m-d H:i:s" );

    // gravar novo protesto
    $mapper->protests->persist($protest);
    $mapper->flush();

    if ( isset($protest->id) || !empty($protest->id) ) 
    {
        // tratar os dados
        $organizer         = new stdClass();
        $organizer->title   = filter_var($_POST['organizer_protest']['title'],   FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $organizer->description = filter_var($_POST['organizer_protest']['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $organizer->protests_id = $protest->id;
        $organizer->facebook = filter_var($_POST['organizer_protest']['facebook'], FILTER_SANITIZE_URL);
        $organizer->twitter = filter_var($_POST['organizer_protest']['twitter'], FILTER_SANITIZE_URL);
        $organizer->site = filter_var($_POST['organizer_protest']['site'], FILTER_SANITIZE_URL);
        $organizer->email = filter_var($_POST['organizer_protest']['email'], FILTER_SANITIZE_EMAIL);
        $organizer->phone1 = filter_var($_POST['organizer_protest']['phone1'], FILTER_SANITIZE_EMAIL);
        $organizer->phone2 = filter_var($_POST['organizer_protest']['phone2'], FILTER_SANITIZE_EMAIL);
        $organizer->created_at = date("Y-m-d H:i:s");
        $organizer->updated_at = date("Y-m-d H:i:s");
        
        // gravar nova organização
        $mapper->organizer_protest->persist($organizer);
        $mapper->flush();
    }

    //redireciona para a novo protesto
    header('HTTP/1.1 201 Created');
    
    return 'Protesto criado com sucesso!'; 
});

$router->put('/protest/*', function ( $id ) use ( $mapper ) 
{
    //pega os dados
    $_POST = json_decode( file_get_contents('php://input'), 1 );

    if ( !isset( $_POST ) || !isset( $_POST['organizer_protest'] ) || v::not(v::arr())->validate( $_POST['organizer_protest'] ) || !isset( $_POST['protest'] ) || v::not( v::arr() )->validate( $_POST['protest'] ) ) 
    {
        header( 'HTTP/1.1 400 Bad Request' );
    
        return 'Faltam parâmetros'; 
    }

    // Validar os dados de protesto
    $validationProtest = v::arr()                                           // verifica se é um array                
                         ->key('title', $rule = v::string()->notEmpty())    // verifica se a key 'title' está vazia   
                         ->key('description', $rule)                        // verifica se a key 'description' está vazia 
                         ->key('date', $rule)                               // verifica se a key 'date' está vazia 
                         ->key('state', $rule)                              // verifica se a key 'state' está vazia 
                         ->key('street', $rule)                             // verifica se a key 'street' está vazia 
                         ->key('postal_code', $rule)                        // verifica se a key 'postal_code' está vazia 
                         ->key('city', $rule)                               // verifica se a key 'city' está vazia 
                         ->key('url', $rule)                                // verifica se a key 'url' está vazia 
                         ->validate( $_POST['protest'] );

    // Validar os dados de organizador
    $validationOrganizer = v::arr()                                         // verifica se é um array                
                         ->key('title', $rule)                              // verifica se a key 'title' está vazia   
                         ->key('description', $rule)                        // verifica se a key 'description' está vazia 
                         ->validate( $_POST['organizer_protest'] );

    if ( !$validationProtest || !$validationOrganizer ) 
    {
        header('HTTP/1.1 400 Bad Request');
    
        return 'Faltam parâmetros'; 
    }

    //buscar protesto por id 
    $protests = $mapper->protests[$id]->fetch();
    
    if ( !$protests ) 
    {
        header('HTTP/1.1 404 Not Found');
    
        return 'Protesto não encontrado!'; 
    }

    // tratar os dados
    $protests->title   = filter_var($_POST['protest']['title'],   FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $protests->description = filter_var($_POST['protest']['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $protests->date =  date('Y-m-d H:i:s', strtotime( $_POST['protest']['date'] ) );
    $protests->street = filter_var($_POST['protest']['street'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $protests->number = filter_var($_POST['protest']['number'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $protests->neighborhood = filter_var($_POST['protest']['neighborhood'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $protests->postal_code = filter_var($_POST['protest']['postal_code'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $protests->complement = filter_var($_POST['protest']['complement'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $protests->reference = filter_var($_POST['protest']['reference'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $protests->city = filter_var($_POST['protest']['city'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $protests->state = filter_var($_POST['protest']['state'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $protests->url = filter_var($_POST['protest']['url'], FILTER_SANITIZE_URL);
    $protests->image = $_POST['protest']['image'];
    $protests->updated_at = date("Y-m-d H:i:s");

    $mapper->protests->persist($protests);

    if ( isset($protests->id) || !empty($protests->id) ) 
    {
        //buscar organizador do protesto por id 
        $organizer_protest = $mapper->organizer_protest( array( 'protests_id' => $protests->id ) )->fetch();
        
        if ( !$organizer_protest ) 
        {
            header('HTTP/1.1 404 Not Found');
        
            return 'Organizador do protesto não encontrado!'; 
        }

        // tratar os dados
        $organizer_protest->title = filter_var($_POST['organizer_protest']['title'],   FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $organizer_protest->description = filter_var($_POST['organizer_protest']['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $organizer_protest->protests_id = $protests->id;
        $organizer_protest->facebook = filter_var($_POST['organizer_protest']['facebook'], FILTER_SANITIZE_URL);
        $organizer_protest->twitter = filter_var($_POST['organizer_protest']['twitter'], FILTER_SANITIZE_URL);
        $organizer_protest->site = filter_var($_POST['organizer_protest']['site'], FILTER_SANITIZE_URL);
        $organizer_protest->email = filter_var($_POST['organizer_protest']['email'], FILTER_SANITIZE_EMAIL);
        $organizer_protest->phone1 = filter_var($_POST['organizer_protest']['phone1'], FILTER_SANITIZE_EMAIL);
        $organizer_protest->phone2 = filter_var($_POST['organizer_protest']['phone2'], FILTER_SANITIZE_EMAIL);
        $organizer_protest->updated_at = date("Y-m-d H:i:s");

        $mapper->organizer_protest->persist($organizer_protest);
        
        $mapper->flush();
    }

    header('HTTP/1.1 200 Ok');
    
    return 'Protesto atualizado!';
});

$router->delete('/protest/*', function ($id) use ($mapper) 
{
    // Validar com negação se string esta preenchida
    if ( !isset($id) || !v::alnum()->notEmpty()->validate($id) ) 
    {
        header('HTTP/1.1 400 Bad Request');
    
        return 'Faltam parâmetros'; 
    }

    //buscar protesto por id 
    $protests = $mapper->protests[$id]->fetch();
    
    if ( !$protests ) 
    {
        header('HTTP/1.1 404 Not Found');
    
        return 'Protesto não encontrado!'; 
    }

    $protests->status = 0;

    //buscar organizador do protesto por id 
    $organizer_protest = $mapper->organizer_protest( array( 'protests_id' => $protests->id ) )->fetch();

    if( $organizer_protest )
        $organizer_protest->status = 0;

    $mapper->protests->persist($protests);
    $mapper->organizer_protest->persist($organizer_protest);

    $mapper->flush();
    
    header('HTTP/1.1 200 Ok');
    
    return 'Protesto removido';
});

$jsonRender = function ($data) 
{
    header('Content-Type: application/json');

    if ( v::string()->validate($data) ) 
    {
        $data = array($data);
    }

    return json_encode($data,true);
};

$router->always('Accept', array('application/json' => $jsonRender));

// para debugar melhor as exceptions
//$router->run();