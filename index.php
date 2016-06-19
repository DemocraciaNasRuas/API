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
 
/** 
 * Rota com autenticação básica
 */

// // do not use this!
// function checkLogin($user, $pass) 
// {
//     return $user === 'admin' && $pass === 'admin';
// }

// $router->get('/admin', function () {
//     return 'RestBeer Admin Protected!';
// })->authBasic('Secret Area', function ($user, $pass) {
//     return checkLogin($user, $pass);
// });

// Rota para listar informações de uma cerveja ou todas
$router->get('/protests/*', function ($data) use ($mapper) 
{
    // Validar com negação se string esta preenchida
    if ( !isset($data) ) 
    {
        $protests = $mapper->protests->fetchAll();

        header('HTTP/1.1 200 Ok');
        
        return $protests;
    }

    // tratar os dados
    $data = filter_var( $data, FILTER_SANITIZE_FULL_SPECIAL_CHARS );

    // validar conteúdo
    if ( v::not(v::alnum()->notEmpty())->validate($data) ) 
    {
        header('HTTP/1.1 404 Not Found');
    
        return 'Não encontrado';
    }

    // buscar protesto por id
    if ( v::int()->validate( $data ) ) 
    {
        // buscar protesto por id
        $protesto = $mapper->cervejas[$data]->fetch();
    } 
    else 
    {
        // buscar protesto pelo nome
        $protesto = $mapper->cervejas(array( 'nome' => $data ))->fetch();
    }

    if ( !$protesto ) 
    {
        header('HTTP/1.1 404 Not Found');
    
        return 'Não encontrado'; 
    }

    header('HTTP/1.1 200 Ok');
    
    return $protesto;
});


$router->post('/protest', function () use ($mapper) 
{
    $_POST = json_decode(file_get_contents('php://input'), 1);

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
                         ->key('city', $rule)                               // verifica se a key 'city' está vazia 
                         ->key('url', $rule)                                // verifica se a key 'url' está vazia 
                         ->validate($_POST['protest']);

    // Validar os dados de organizador
    $validationOrganizer = v::arr()                                         // verifica se é um array                
                         ->key('title', $rule)    // verifica se a key 'title' está vazia   
                         ->key('description', $rule)                        // verifica se a key 'description' está vazia 
                         ->validate($_POST['organizer_protest']);

    if ( !$validationProtest || !$validationOrganizer ) 
    {
        header('HTTP/1.1 400 Bad Request');
    
        return 'Faltam parâmetros'; 
    }

    // tratar os dados
    $protest         = new stdClass();
    $protest->title   = filter_var($_POST['protest']['title'],   FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $protest->description = filter_var($_POST['protest']['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $protest->date = $_POST['protest']['date'];
    $protest->state = filter_var($_POST['protest']['state'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $protest->city = filter_var($_POST['protest']['city'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $protest->url = filter_var($_POST['protest']['url'], FILTER_SANITIZE_URL);
    $protest->image = $_POST['protest']['image'];

    // gravar novo protesto
    $mapper->protests->persist($protest);
    $mapper->flush();

    if ( isset($protest->id) || !empty($protest->id) ) 
    {
        // tratar os dados
        $organizer         = new stdClass();
        $organizer->title   = filter_var($_POST['organizer_protest']['title'],   FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $organizer->description = filter_var($_POST['organizer_protest']['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $organizer->protest_id = $protest->id;
        $organizer->facebook = filter_var($_POST['organizer_protest']['facebook'], FILTER_SANITIZE_URL);
        $organizer->twitter = filter_var($_POST['organizer_protest']['twitter'], FILTER_SANITIZE_URL);
        $organizer->site = filter_var($_POST['organizer_protest']['site'], FILTER_SANITIZE_URL);
        $organizer->email = filter_var($_POST['organizer_protest']['email'], FILTER_SANITIZE_EMAIL);
        $organizer->phone1 = filter_var($_POST['organizer_protest']['phone1'], FILTER_SANITIZE_EMAIL);
        $organizer->phone2 = filter_var($_POST['organizer_protest']['phone2'], FILTER_SANITIZE_EMAIL);
        
        // gravar nova organização
        $mapper->organizer_protest->persist($organizer);
        $mapper->flush();
    }

    //redireciona para a nova cerveja
    header('HTTP/1.1 201 Created');
    
    return 'Protesto criado com sucesso!'; 
});

$router->put('/protest/*', function ($nome) use ($mapper) 
{
    //pega os dados
    parse_str(file_get_contents('php://input'), $data);

    if ( !isset($_POST) || !isset($_POST['organizer']) || v::not(v::arr())->validate($_POST['organizer']) || !isset($_POST['protest']) || v::not(v::arr())->validate($_POST['protest']) ) 
    {
        header('HTTP/1.1 400 Bad Request');
    
        return 'Faltam parâmetros'; 
    }

    // Validar o input
    $validation = v::arr()                                                        // validar se é array                  
                 ->key('nome',   $rule = v::alnum()->notEmpty()->noWhitespace())  // validar a key 'nome' se não está vazia   
                 ->key('estilo', $rule)                                           // utilizando a mesma regra da key de cima      
                 ->validate($data['organizer']);

    // Validar o input
    $validation = v::arr()                                                        // validar se é array                  
                 ->key('nome',   $rule = v::alnum()->notEmpty()->noWhitespace())  // validar a key 'nome' se não está vazia   
                 ->key('estilo', $rule)                                           // utilizando a mesma regra da key de cima      
                 ->validate($data['protest']);

    if ( !$validation ) 
    {
        header('HTTP/1.1 400 Bad Request');
    
        return 'Faltam parâmetros'; 
    }

    // tratar os dados
    $nome = filter_var( $nome, FILTER_SANITIZE_FULL_SPECIAL_CHARS );

    // validar conteúdo
    if ( v::not(v::alnum()->notEmpty())->validate($nome) ) 
    {
        header('HTTP/1.1 404 Not Found');
    
        return 'Não encontrada';
    }

    // buscar cerveja pelo nome
    $cerveja = $mapper->cervejas(array( 'nome' => $nome ))->fetch();

    // BONUS - podemos buscar por id também 
    // $cerveja = $mapper->cervejas[$id]->fetch();

    if ( !$cerveja ) 
    {
        header('HTTP/1.1 404 Not Found');
    
        return 'Não encontrada'; 
    }

    // tratar os dados
    $newNome   = filter_var( $data['cerveja']['nome'],   FILTER_SANITIZE_FULL_SPECIAL_CHARS );
    $newEstilo = filter_var( $data['cerveja']['estilo'], FILTER_SANITIZE_FULL_SPECIAL_CHARS );

    //Persiste na base de dados ($mapper retorna objeto preenchido full)
    $cerveja->nome   = $newNome;
    $cerveja->estilo = $newEstilo;
    $mapper->cervejas->persist($cerveja);
    $mapper->flush();

    header('HTTP/1.1 200 Ok');
    
    return 'Protesto atualizada';
});

$router->delete('/protest/*', function ($nome) use ($mapper) 
{
    // tratar os dados
    $nome = filter_var( $nome, FILTER_SANITIZE_FULL_SPECIAL_CHARS );

    // Validar com negação se string esta preenchida
    if ( !isset($nome) || v::not(v::alnum()->notEmpty())->validate($nome) ) 
    {
        header('HTTP/1.1 400 Bad Request');
    
        return 'Faltam parâmetros'; 
    }

    // verificar se existe a cerveja pelo nome
    $cerveja = $mapper->cervejas(array( 'nome' => $nome ))->fetch();

    // BONUS - podemos buscar por id também 
    // $cerveja = $mapper->cervejas[$id]->fetch();
    
    if ( !$cerveja ) 
    {
        header('HTTP/1.1 404 Not Found');
    
        return 'Não encontrada'; 
    }

    $mapper->cervejas->remove($cerveja);
    $mapper->flush();
    
    header('HTTP/1.1 200 Ok');
    
    return 'Cerveja removida';
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