<?php

it('returns a paidful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
