<?php
// Routes

$app->get('/', function ($request, $response) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");
    // Render index view
    return $this->renderer->render($response, 'index.phtml');
});

$app->get('/api', function ($request, $response) {
    return $this->renderer->render($response, 'index-api.phtml');
});

$app->get('/api/users[/search/{name}]', function ($request, $response, $args) {
    if (isset($args['name'])) {
        $stm = $this->db->prepare('SELECT * FROM `tel_book` WHERE `name` = :name');
        $stm->bindParam(':name', $args['name']);
        $stm->execute();
    } else {
        $stm = $this->db->query('SELECT * FROM tel_book');
    }
    $result = $stm->fetchAll(PDO::FETCH_ASSOC);
    $newResponse = $response->withJson($result, 200);
    return $this->renderer->render($newResponse, 'response.phtml');
});

$app->post('/api/users', function ($request, $response) {
    $params = $request->getParsedBody();
    if ($params['name'] && $params['telephone']) {
        $stm = $this->db->prepare('INSERT INTO tel_book (name, telephone) VALUES (:name, :telephone)');
        $stm->bindParam(':name', $params['name']);
        $stm->bindParam(':telephone', $params['telephone']);
        if ($stm->execute())
            $newResponse = $response->write('User saved, ID: ' . $this->db->lastInsertId());
    } else {
        $newResponse = $response->write('Not enough parameters.');
        $newResponse = $response->withStatus(400);
    }
    return $this->renderer->render($newResponse, 'response.phtml');
});

$app->put('/api/users/{id}', function ($request, $response, $args) {
    if ($args['id']) {
        $params = $request->getParsedBody();
        if (isset($params['name']) || isset($params['telephone'])) {
            $sql = 'UPDATE tel_book SET ';
            if (isset($params['name']))
                $sql .= 'name = :name';
            if (isset($params['name']) && isset($params['telephone']))
                $sql .= ', ';
            if (isset($params['telephone']))
                $sql .= 'telephone = :telephone';
            $sql .= ' WHERE id = :id';
            $stm = $this->db->prepare($sql);
            if (isset($params['name']))
                $stm->bindParam(':name', $params['name']);
            if (isset($params['telephone']))
                $stm->bindParam(':telephone', $params['telephone']);
            $stm->bindParam(':id', $args['id']);
            if ($stm->execute())
                $newResponse = $response->write('User info updated.');
        } else {
            $newResponse = $response->write('Not enough parameters.');
            $newResponse = $response->withStatus(400);
        }
    }
    return $this->renderer->render($newResponse, 'response.phtml');
});

$app->delete('/api/users/{id}', function ($request, $response, $args) {
    if ($args['id']) {
        $stm = $this->db->prepare('DELETE FROM tel_book WHERE id = :id');
        $stm->bindParam(':id', $args['id']);
        if ($stm->execute()) {
            $newResponse = $response->write('User ' . $args['id'] . ' is deleted.');
            $newResponse = $response->withStatus(204);
        } else {
            $newResponse = $response->write('User ' . $args['id'] . ' is NOT deleted.');
            $newResponse = $response->withStatus(304);
        }
    }
    return $this->renderer->render($newResponse, 'response.phtml');
});

