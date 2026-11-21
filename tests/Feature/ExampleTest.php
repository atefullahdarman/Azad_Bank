<?php

it('starts at the login page for guests', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});
