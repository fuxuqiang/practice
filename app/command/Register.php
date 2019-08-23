<?php
namespace app\command;

class Register
{
    public function handle()
    {
        $letters = array_merge(range('a', 'z'), range('A', 'Z'));

        $inputs = array_merge($letters, range(0, 9), ['_']);

        $mh = curl_multi_init();

        for ($i=0; $i < 10; $i++) { 
            $ch = curl_init('http://auth.test/register');

            $name = $letters[mt_rand(0, 51)];
            for ($j=0; $j < mt_rand(0, 3); $j++) { 
                $name .= $inputs[mt_rand(0, 62)];
            }
            $password = '';
            for ($k=0; $k < mt_rand(6, 9); $k++) { 
                $password .= $inputs[mt_rand(0, 62)];
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, compact('name', 'password'));

            curl_multi_add_handle($mh, $ch);
        }

        $active = null;
        do {
            curl_multi_exec($mh, $active);
            usleep(100000);
        } while ($active);
    }
}
