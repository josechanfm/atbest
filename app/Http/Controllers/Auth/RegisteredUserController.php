<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Organization;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Registration');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'registration_code' => 'required|exists:organizations,registration_code',
        ]);
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $organization=Organization::where('registration_code',$request->registration_code)->first();
        $organization->members()->create([
            'user_id'=>$user->id,
            'given_name'=>$request->given_name,
            'middle_name'=>$request->middle_name??null,
            'family_name'=>$request->family_name,
            'display_name'=>$request->name,
            'email'=>$request->email,
            'is_default'=>true,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }

    public function sendSMSVarificationCode(Request $request): RedirectResponse
    {
        $request->validate([
            'phone_number' => 'required|string|phone:AUTO',
        ]); 
        
		$sms_from=env('SMS_FROM');
		$sms_username=env('SMS_USER');
		$sms_password=env('SMS_PWD');
		$sms_url=env('SMS_URL');

		
		if( $message_language == 'full_english'){
			$language = "";
		}else{
			$language = "&locale=utf-8";
			$content = urlencode($content);
		}


		foreach ($recipients as $key => $value) {
			if(strpos($value, "853") !== false){
				$to = trim($value," ");
			}else{
				$to = "853" . trim($value," ");
			}
			$url = $sms_url."/"."?username=".$sms_username."&password=".$sms_password."&from=".$sms_from."&to=".$to.$language."&text=".$content;
		
			

			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


			//for debug only!
			// curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			// curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

			$resp = curl_exec($curl);
			curl_close($curl);

			$str = (string) $resp;
			// 返回 id 即是成功, 檢查是不是全數字且長度大於7
			// 1. 如果是純數字(Float/Int)，用 number_format 強制轉成完整數字字串，避免科學記號
			// 2. 如果是字串，直接處理
			if (is_numeric($resp)) {
				$str = number_format($resp, 0, '', '');
			} else {
				$str = (string)$resp;
			}

			// 3. 使用正則表達式移除所有「非數字」的字元 (包含逗號、空格、點、E+等)
			$cleanStr = preg_replace('/\D/', '', $str);

			// 4. 檢查清理後的字串長度是否大於 7
			// 注意：這裡不需再用 is_numeric，因為 preg_replace 已經保證剩下的全是數字
			if (strlen($cleanStr) > 7) {
				$status = 'success';
			} else {
				$status = 'fail';
			}
			
			$this->db->insert('sms_responses', array(
				'sms_id' => $sms_id, 
				'status' => $status, 
				'number' => $to, 
				'response' => $resp 
			));

    }
}
