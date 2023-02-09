<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use PhpParser\Node\Stmt\TryCatch;

class ProviderController extends Controller
{

  public function redirect($provider)
  {
    return Socialite::driver($provider)->redirect();
  }


  public function callback($provider)
  {
   

    try {
      $SocialUser = Socialite::driver($provider)->user();
     
      if (User::where('email', $SocialUser->getEmail())->exists()) {
        return redirect('/login')->withErrors(['email' => 'This email uses different method to login.']);
      }

      $user = User::where([
        'provider' => $provider,
        'provider_id' => $SocialUser->getId()
      ])->first();
    
      if (!$user) {
      
       $user = User::create([
        'name' => $SocialUser->getName(),
        'email' => $SocialUser->getEmail(),
        'username' => User::generateUserName($SocialUser->getNickname()),
        'provider' => $provider,
        'provider_id' => $SocialUser->getId(),
        'provider_token' => $SocialUser->token,
        'email_verified_at' => now()

       ]);       
         
      }
      
      Auth::login($user);

      return redirect()->route('/dashboard');

    } catch (\Exception $e) {
      return redirect('/login');
    }
  }
}
