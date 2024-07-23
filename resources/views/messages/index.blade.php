<style type="text/css">
    .emoji-list {
    margin-top: 4px;
}
.emoji-list ul {
    list-style-type: none;
}
.emoji-list ul li {
    display: inline-block;
    margin: 3px;
}
.btn:not(:disabled):not(.disabled) {
    cursor: pointer;
}
.input-text {
    position: relative;
    display: flex;
    flex-wrap: wrap;
    align-items: stretch;
    width: 100%;
}
.upload-btn {
    font-size: 19px;
    color: #2b2be6;
}
.btn-chat {
    font-size: 11px;
    font-weight: bold;
}
</style>
<div class="message-wrapper">
    <ul class="messages">
        @foreach($messages as $message)
            <li class="message clearfix">
            {{-- if message from id is equal to auth id then it is sent by logged in user --}}
                <div class="{{ ($message->from == Auth::id()) ? 'sent' : 'received' }}">
                    @if($message->message)
                        <p>{!! $message->message !!}</p>
                    @elseif($message->image)
                        <div style="width: 200px; height: 200px;"><img class="img-responsive" src="{{$message->image}}" /></div>
                    @endif
                    <p class="date">{{ date('d M y, h:i a', strtotime($message->created_at)) }}</p>
                </div>
            </li>
        @endforeach
    </ul>
</div>

<div class="input-text">
    <form method="post" enctype="multipart/form-data" id="image_form" class="upload-frm" style="display: none;">
        <input type="file" name="files" class="image" accept="image/png, image/gif, image/jpeg"  />
    </form>
    <button type="button" class="btn btn-default btn-sm upload-btn">
        <i class="glyphicon glyphicon-picture"></i>
    </button>
    <input type="text" name="message" class="submit chat_input">
    <button class="btn btn-primary btn-sm btn-chat" type="button" data-to-user="" disabled>
    <i class="glyphicon glyphicon-send"></i>
    Send</button>
</div>
<div class="emoji-list">
                        <ul>
                            <li><a href="javascript:void(0);" class="emoji">&#128512;</a></li>
                            <li><a href="javascript:void(0);" class="emoji">&#128513;</a></li>
                            <li><a href="javascript:void(0);" class="emoji">&#128514;</a></li>
                            <li><a href="javascript:void(0);" class="emoji">&#128515;</a></li>
                            <li><a href="javascript:void(0);" class="emoji">&#128516;</a></li>
                            <li><a href="javascript:void(0);" class="emoji">&#128517;</a></li>
                            <li><a href="javascript:void(0);" class="emoji">&#128518;</a></li>
                            <li><a href="javascript:void(0);" class="emoji">&#128519;</a></li>
                            <li><a href="javascript:void(0);" class="emoji">&#128520;</a></li>
                            <li><a href="javascript:void(0);" class="emoji">&#128521;</a></li>
                            <li><a href="javascript:void(0);" class="emoji">&#128522;</a></li>
                            <li><a href="javascript:void(0);" class="emoji">&#128523;</a></li>
                            <li><a href="javascript:void(0);" class="emoji">&#128524;</a></li>
                            <li><a href="javascript:void(0);" class="emoji">&#128525;</a></li>
                            <li><a href="javascript:void(0);" class="emoji">&#128526;</a></li>
                            <li><a href="javascript:void(0);" class="emoji">&#128527;</a></li>
                            <li><a href="javascript:void(0);" class="emoji">&#128528;</a></li>
                            <li><a href="javascript:void(0);" class="emoji">&#128529;</a></li>
                        </ul>
                    </div>
