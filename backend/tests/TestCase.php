<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    /**
     * Set up the test case.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure all requests are treated as JSON API requests
        $this->withoutVite();

        // Add custom test response macro for validation errors
        TestResponse::macro('assertApiValidationErrors', function (array $keys) {
            $this->assertStatus(422);
            $this->assertJson(['success' => false]);
            $this->assertJsonPath('error.code', 'VALIDATION_ERROR');

            foreach ($keys as $key) {
                $this->assertJsonPath("error.errors.{$key}", fn($value) => !empty($value));
            }

            return $this;
        });
    }

    /**
     * Visit a URI with a JSON request.
     *
     * @param  string  $method
     * @param  string  $uri
     * @param  array  $data
     * @param  array  $headers
     * @param  int  $options
     * @return \Illuminate\Testing\TestResponse
     */
    public function json($method, $uri, array $data = [], array $headers = [], $options = 0)
    {
        $headers = array_merge([
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ], $headers);

        return parent::json($method, $uri, $data, $headers, $options);
    }
}
