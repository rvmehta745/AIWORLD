<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
</head>

<body>
    <table bgcolor="#fff" cellpadding="0" cellspacing="0" height="100%" width="100%">
        <tbody>
            <tr>
                <td align="center" valign="top" class="">
                    <div style="max-width:700px;">
                        <table cellpadding="0" cellspacing="0" style="width:100%;margin-top:0;font-family:Arial,Helvetica,sans-serif;border:1px solid #eeebeb;background-color: #ebebeb; ">
                            <tbody>
                                <tr>
                                    <td align="left " style="padding:5px;font-family:Arial,Helvetica,sans-serif " width=" 40% ">
{{--                                        <img src="" style="max-width:100%;width:150px; " alt="logo " alt="">--}}

                                    </td>
                                    <!-- {{-- <td align="right " style="font-family:Arial,Helvetica,sans-serif " valign="middle " width="54% " class=" "><a href="" style="font-size: 14px;color: #43abf4; text-decoration:
                            none ">Sign in</a></td> --}} -->
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>

                        <table bgcolor="#FFFFFF " cellpadding="0 " cellspacing="0 " style="margin:0 auto;width:100%;background-color: #fff;background-color:white;border:1px solid #ccc ">
                            <tbody>
                                <tr>
                                    <td style="padding:50px;font-size:14px;font-family:Arial,Helvetica,sans-serif " class=" ">

                                        <p>Hello {{ $bodyFields['name'] }} ,</p>

                                        <p style="margin:0;padding:0;margin-bottom:30px;font-size:15px;display:block;color:#000; " class=" ">You recently requested to reset your password. Please click the button below to change the password.</p>

                                        <p style="display:block;margin-bottom:20px;font-size:14px;font-weight:bold;line-height:1.4em " class=" ">Note: The password reset link would be valid for 24 hour.<br></p>

                                        <p style="display:block;margin-bottom:20px;font-size:14px;line-height:1.4em " class=" "><b><strong class=" ">Your OTP code is:</strong></b></p>
                                        
                                        <p style="display:block;margin-bottom:20px;font-size:24px;font-weight:bold;text-align:center;letter-spacing:5px;color:#43abf4;" class=" ">{{ $bodyFields['otp'] }}</p>

                                        <p style="display:block;margin-bottom:20px;font-size:14px;line-height:1.4em " class=" "><b><strong class=" ">Or click this link to verify Otp :</strong></b></p>

                                        <table cellpadding="0 " cellspacing="0 " style="width:100% ">
                                            <tbody>
                                                <tr>
                                                    <td style="font-size:14px;font-weight:bold;line-height:1.4em;font-family:Arial,Helvetica,sans-serif;padding:20px;border:1px solid #a7cae5;background:#e3f3ff;text-align:left;background-color:#e3f3ff
                            " class=" "><a href="{{ $bodyFields[ 'url'] }} " style="font-size:14px;word-break:break-all;font-weight:bold;color:#43abf4;text-decoration:underline!important " target="_blank ">verify your Otp</a></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        &nbsp;

                                        <p style="font-size:14px;font-family:Arial,Helvetica,sans-serif;line-height:1.4em ">Sincerely,<br></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table cellpadding="0 " cellspacing="0 " style="margin:0 auto;width:100%;margin-top:0;background-color: #ebebeb ">
                            <tbody>
                                <tr>
                                    <td style="padding:10px;text-align:center;font-size:12px;color:#616060;border:1px solid #eeebeb;font-family:Arial,Helvetica,sans-serif ">If you didn't initiate the request, you don't need to take any further action and can safely disregard this email.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</body>

</html>
