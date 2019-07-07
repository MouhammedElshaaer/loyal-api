<?php

namespace App\Http\Requests\Traits;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

trait UsesCustomErrorMessage
{
  /**
   * Handle a failed validation attempt.
   *
   * @param  \Illuminate\Contracts\Validation\Validator  $validator
   * @return void
   *
   * @throws \Illuminate\Http\Exceptions\HttpResponseException
   */
  protected function failedValidation(Validator $validator)
  {
    /**
     * Those next line of codes calls the message function exists in the Request classes
     * to return their custom message
     */
    // $message = (method_exists($this, 'message'))
    //   ? $this->container->call([$this, 'message'])
    //   : 'The given data was invalid.';
    
    throw new HttpResponseException(response()->json([
      'code' => 400,
      'message' => $validator->errors()->first(),
      'data' => new \stdClass()
    ], 200));
  }
}