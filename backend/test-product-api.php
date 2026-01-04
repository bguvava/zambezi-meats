<?php

use App\Models\Product;
use App\Http\Resources\Api\V1\ProductResource;

$product = Product::with('images')->find(1);
$resource = new ProductResource($product);
echo json_encode($resource->toArray(request()), JSON_PRETTY_PRINT);
