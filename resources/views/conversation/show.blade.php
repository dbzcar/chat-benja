@extends('layouts.app')
@section('additional_css')
    <style>
        .panel-body{
            height: 50vh;
            overflow-y: scroll;
            padding-right: 15px;
            padding-left:15px;
        }
        .message{
            padding: 10pt;
            border-radius: 5pt;
            margin: 5pt;
            max-width:50%;
            word-wrap: break-word;

        }
        .owner{
            background-color: #5583fd;
            float: right;
        }
        .not_owner{
            background-color: #eaeff2;
            float:left;
        }

        .row-owner{
            flex-direction: row;
            justify-content: flex-end;
            /*margin:0;*/            
        }
        .row-not-owner{
            flex-direction: row;
            justify-content: flex-start;
            /*margin:0;*/           
        }

        .contenedor{
            display:flex;
            justify-content: center;
        }

        .panel-default {
            border-color: #d3e0e9;
        }
        .panel {
            margin-bottom: 22px;
            background-color: #fff;
            border: 1px solid;
            border-top-color: #bfd8f7;
            border-right-color: #bfd8f7;
            border-bottom-color: #bfd8f7;
            border-left-color: #bfd8f7;
            border-radius: 4px;
            box-shadow: 0 1px 1px rgba(0,0,0,.05);
            }

        .panel-heading {
            padding: 10px 15px;
            border-bottom: 1px solid;
            border-bottom-color:#bfd8f7;
            border-top-right-radius: 3px;
            border-top-left-radius: 3px;
        }

        .panel-footer {
            padding: 10px 15px;
            background-color: #f5f5f5;
            border-top: 1px solid #d3e0e9;
            border-bottom-right-radius: 3px;
            border-bottom-left-radius: 3px;
        }

    </style>
@endsection
@section('content')
    <div class="contenedor">
        <div class="col-md-offset-2 col-md-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-8">
                            <h5>Estas hablando con: {{($conversation->user1()->first()->id==Auth::user()->id)?$conversation->user2()->first()->name:$conversation->user1()->first()->name}}</h5>
                        </div>
                    </div>
                </div>
                <div class="panel-body" id="panel-body">
                    @foreach($conversation->messages as $message)
                        @if($message->user_id!=Auth::user()->id)
                        <div class="row row-not-owner">
                            <div class="message {{ ($message->user_id!=Auth::user()->id)?'not_owner':'owner'}}">
                                {{$message->text}}<br/>
                                <b>{{$message->created_at->diffForHumans()}}</b>
                            </div>
                        </div>
                        @else
                        <div class="row row-owner">
                            <div class="message {{ ($message->user_id!=Auth::user()->id)?'not_owner':'owner'}}">
                                {{$message->text}}<br/>
                                <b>{{$message->created_at->diffForHumans()}}</b>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
                <div class="panel-footer">
                        <textarea id="msg" class="form-control" placeholder="Escribe tu mensaje"></textarea>
                        <input type="hidden" id="csrf_token_input" value="{{csrf_token()}}"/>
                        <br/>
                        <div class="row">
                            <div class="col-md-offset-4 col-md-4">
                            <button class="btn btn-primary btn-block" onclick="button_send_msg()">Enviar</button>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('additional_js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.1.1/socket.io.js"></script>
    <script>
        var socket = io.connect('http://127.0.0.1:8890');
        socket.emit('add user', {'client':{{Auth::user()->id}},'conversation':{{$conversation->id}} });

        socket.on('message', function (data) {
            $('#panel-body').append(
                    '<div class="row row-not-owner">'+
                    '<div class="message not_owner">'+
                    data.msg+'<br/>'+
                    '<b>now</b>'+
                    '</div>'+
                    '</div>');

            scrollToEnd();

         });
    </script>
    <script>
        $(document).ready(function(){
            scrollToEnd();

            $(document).keypress(function(e) {
                if(e.which == 13) {
                    var msg = $('#msg').val();
                    $('#msg').val('');//reset
                    send_msg(msg);
                }
            });
        });

        function button_send_msg(){
            var msg = $('#msg').val();
            $('#msg').val('');//reset
            send_msg(msg);
        }


        function send_msg(msg){
            $.ajax({
                headers: { 'X-CSRF-Token' : $('#csrf_token_input').val() },
                type: "POST",
                url: "{{route('message.store')}}",
                data: {
                    'text': msg,
                    'conversation_id':{{$conversation->id}},
                },
                success: function (data) {
                    if(data==true){

                        $('#panel-body').append(
                                '<div class="row row-owner">'+
                                '<div class="message owner">'+
                                msg+'<br/>'+
                                '<b>ora</b>'+
                                '</div>'+
                                '</div>');

                        scrollToEnd();
                    }
                },
                error: function (e) {
                    console.log(e);
                }
            });
        }

        function scrollToEnd(){
            var d = $('#panel-body');
            d.scrollTop(d.prop("scrollHeight"));
        }

    </script>
@endsection
