@extends('email.layouts.main')
@section('content')
<style>
    .email-temp-col{
        margin: 20px auto;
    }
    .space-temp{
        padding:0 30px;
    }
</style>
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ebebeb">
    <tr>
        <td class="p30-15-0 space-temp"  bgcolor="#ffffff">
            <div style="font-family: Helvetica, Arial, sans-serif; overflow: auto; line-height: 2; text-align: center;">
                <div class="email-temp-col">
                    <div style="border-bottom: 1px solid #eee; padding-bottom: 10px;">
                        <a href="{{ url('/') }}" style="font-size: 18px; color: #00466a; text-decoration: none; font-weight: 600;">
                            Laravel Demo
                        </a>
                    </div>
                    <div style="text-align: left; margin-top: 20px; font-size:20px;">
                        {{ $otp }}
                    </div>
                </div>
            </div>        
        </td>
    </tr>
</table>
@endsection
