@extends('layouts.base')

@section('css')
    <link rel='stylesheet' href='/css/signup.css' />
@endsection

@section('content')
    <div class='col-md-6'>
        <h2 class='title'>绑定Google Authenticator</h2>
        <h4>“没有网络安全就没有国家安全，就没有经济社会稳定运行，广大人民群众利益也难以得到保障。”<p>——2018年4月20日至21日，习近平在全国网络安全和信息化工作会议上发表讲话</h4>

        <form action='/token_bind' method='POST'>
            用户名: <input type='text' name='UserName' class='form-control form-field' value="{{$user}}" readonly />
            SecretKey: <input type='text' name='SecretKey' class='form-control form-field' value="{{$key}}" readonly />
            请使用验证器扫描：
            <img src="data:image/png;base64, {{$QRCode}}"/><p>
            输入一组验证码: <input type='text' name='InputKey' class='form-control form-field' placeholder="Verify Key"/>
            <input type="hidden" name='_token' value='{{csrf_token()}}' />
            <input type="submit" class="btn btn-default btn-success" value="Bind"/>
        </form>
    </div>

@endsection
