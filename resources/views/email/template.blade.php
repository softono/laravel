@extends('email.layouts.main')
@section('content')
<style>
    .email-temp-col{
        margin: 20px auto;
    }
    .space-temp{
        padding:30px;
    }
    .temp-body{
        margin:20px 30px;
    }
</style>
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ebebeb">
    <tr>
        <td class="p30-15-0 space-temp" bgcolor="#ffffff">
            <div style="padding:30px; font-family: Helvetica, Arial, sans-serif; overflow: auto; line-height: 2; text-align: center; font-size:18px;">
                <div class="email-temp-col">
                   
                    <div class="temp-body">
                        {!! $body !!}
                    </div>
                </div>
            </div>        
        </td>
    </tr>
</table>
@endsection
