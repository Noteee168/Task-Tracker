<?php

it('returns a successful redirect to login for guest', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});
