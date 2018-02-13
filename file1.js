$("#success_message").delay(3200).fadeOut(300);
$(document).on('click','.add_discussion',function(){
   if($('#loginstatus').val()==''){
       bootbox.alert("Please Login to Add Post on community");
       return false;
   }else{
        if($('.comment-box').is(':visible')){
            $('.comment-box').hide();
        }else{
            $('.comment-box').show();
        }
    }
});

$(window).scroll(function() {
    if (document.body.scrollHeight == document.body.scrollTop +  window.innerHeight){
        sum=0;
        pass_limit=$('#limitpass').val();
        var url=$('#baseurl').val();
        var cat_id=$('#catid').val();
        if($('#connectend').val()=='yes'){
            $.ajax({
                url:url+"data/getdata",
                dataType: "html",
                data : {cat_id:cat_id,pass_limit:pass_limit},
                type: "POST",
                beforeSend: function(data){
                    
                },
                success: function(data){
                    var sum=parseInt($('#limitpass').val())+parseInt(5);
                    $('#community_result').append(data);
                    $('#limitpass').val(sum);
                    if($('#notfound').length>0){
                        $('#connectend').val('no');
                        $('#limitpass').val('5');
                    }
                },
                error:function(data){
                }
            });
        }else{
            //$('#notfound').html("No record found");
        }
    }      
});

$(document).on('click','.commentSubmit',function(){
    var this_pointer=$(this);
    var url=$('#baseurl').val();
    var comment_id=$(this).prev('input.comment_id').val();
    var get_val=$(this).prev().prev('div').children('textarea').val();
    if(get_val==''){
        $(this).prev().prev('div').children('p').html('please write comment');
    }else{
        $(this).prev('div').children('p').html('');
        $.ajax({
            url:url+"/communiti/data",
            dataType:"json",
            type: "post",
            data : {comment:get_val,comment_id:comment_id},
            beforeSend: function(){
                
            },
            success: function(data) {
                var z=data[0]['result'].split('/');
                var x='';
                if(z[1]==''){
                     var imgval1="<img class='image-circle-small img-circle user-photo' src='"+url+"profileImage/defaultuser.png"+"'/>";
                }else{
                     var imgval1="<img class='image-circle-small img-circle user-photo' src='"+url+"profileImage/"+data[0]['user_id']+"/"+z[1]+"'/>";
                }
                x=x+"<li class='comment'><div class='comment-body'>";
                    x=x+"<div class='comment-author vcard'>";
                        x=x+imgval1;
                            x=x+"<cite class='fn'><b>&nbsp;&nbsp;"+z[0]+"</b></cite>";
                                x=x+"<span class='says'>&nbsp;&nbsp;says:</span>";
                    x=x+"</div>";
                    x=x+"<div class='comment-meta commentmetadata'>";
                        x=x+"<a href='#'>"+data[0]['datetime_added']+"</a>";
                        x=x+"<p>"+data[0]['comment']+"</p>";
                    x=x+"</div>";
                    x=x+"<div class='col-sm-12'>";
                        x=x+"<span id='49' class='feed_comment_like'><i class='fa fa-thumbs-o-up' aria-hidden='true'></i>";
                            x=x+"<span></span>";
                        x=x+"</span>";
                    x=x+"</div>";
                    x=x+"<h4>Leave a Reply</h4>";
                    x=x+"<div class='reply_result"+data[0]['id']+"'>";
                    x=x+"<div id='comment-message' class='form-row'>";
                        x=x+"<textarea name='comment' class='rplytextarea' placeholder='Message' id='comment-box'></textarea>";
                        x=x+"<p class='has-error help-block help-block-error'></p>";
                    x=x+"</div>";
                    x=x+"<div class='reply'><a id='"+data[0]['id']+"' rel='nofollow' class='rply_comment btn btn-default pull-right' href='javascript:void(0)'>Reply</a>";
                    x=x+"</div>";
                    x=x+"</div></li>";
                    $('.ajaxfeedlist'+comment_id+'').prepend(x);
                    $(this_pointer).parent().hide('slow');
                    $('textarea').val('');
            },
            error :function(data){

            }
        });
         
    }
});
$(document).on('click','.view_commenrmore',function(){
    $(this).parent().prev('ul').children('li').show('slow');
    $(this).html('hide');
    $(this).removeClass('view_commenrmore');
    $(this).addClass('hide_commenrmore');
});
$(document).on('click','.hide_commenrmore',function(){
    $(this).parent().prev('ul').children('li:not(:eq(0))').hide('slow');
    $(this).html('VIEW MORE');
    $(this).removeClass('hide_commenrmore');
    $(this).addClass('view_commenrmore');
});
$(document).on('click','.rply_comment',function(){
    if($('#loginstatus').val()==''){
       bootbox.alert("Please Login to Add Reply on community");
       return false;
    }else{
        var comment_id=$(this).attr('id');
        var value_area=$(this).parent().prev('div').children('textarea').val();
        var thispointer=$(this);
        var url=$('#baseurl').val();
        if(value_area==''){
            $(this).parent().prev('div').children('p').html('Please write Reply');
            return false;
        }else{
            $.ajax({
                url:url+"community/communitycommentrplyinsert",
                dataType:"json",
                type: "post",
                data : {comment_id:comment_id,value_area:value_area},
                beforeSend: function(){

                },
                success: function(data) {
                    var comment_rply=data;
                    var res_comment='';
                    if(data[0]=='true'){
                        var z=data[1][0]['result'].split('/');
                        if(z[1]==''){
                            var imgval_rply="<img class='image-circle-small img-circle user-photo' src='"+url+"profileImage/defaultuser.png"+"'/>";
                        }else{
                            var imgval_rply="<img class='image-circle-small img-circle user-photo' src='"+url+"profileImage/"+data[1][0]['user_id']+"/"+z[1]+"'/>";
                        }
                        res_comment=res_comment+"<div class='col-md-9 rply_comment'>"+comment_rply[1][0]['reply']+"</div><div class='col-md-3'>"+imgval_rply+"&nbsp;<a><b>"+z[0]+"</b></a><br/>"+comment_rply[1][0]['datetime_added']+"</div>";

                    }
                    $('.reply_result'+comment_id+'').prepend(res_comment);
                    $('.rplytextarea').val('');
                },
                error :function(data){

                }
            });
        }
    }
});
\
$(document).on('click','.comment_button',function(){
    if($('#loginstatus').val()==''){
       bootbox.alert("Please Login to Like on community");
       return false;
    }else{
        var thispointer=$(this);
        var url=$('#baseurl').val();
        $.ajax({
            url:url+"communitylike",
            dataType: "json",
            data : {pass_communityid:$(this).attr('id')},
            type: "post",
            beforeSend: function(){
                
            },
            success: function(data) {
                var sum=0;
                if(data[0]=='true'){
                    if(data[1]=='delete'){
                       var sum=parseInt($(thispointer).children('span').text());
                       sum +=-1;
                       $(thispointer).children('span').text(sum);
                    }else{
                       var sum=parseInt($(thispointer).children('span').text());
                       sum +=+1;
                       $(thispointer).children('span').text(sum);
                    }
                }else{

                }
            },
            error :function(data){

            }
        });
    }
});

$(document).on('click','.delete_discussion',function(){
    $(this).next('ul').toggle();
});
$('html').click(function (e) {
    if (e.target.id == 'deletediscussionid') {
    } else {
        $('ul.dropdeletediscussion').hide();
    }
});
$(document).on('click','.deletediscussion',function(){
    var id=$(this).attr('id');
    var url=$('#baseurl').val();
    bootbox.confirm("<a href='#'><i class='fa fa-exclamation-circle fa-2x' aria-hidden='true'></i></a>Are you sure you want to Delete Post?", 
    function(result) {
        if(result === true ){
            $.ajax({
                url:url+"communitydelete",
                dataType: "json",
                data : {id:id},
                type: "post",
                beforeSend: function(){
                    
                },
                success: function(data) {
                    bootbox.alert("You have successfully Deleted Your Post");
                },
                error :function(data){
                    bootbox.alert("There is a issue");
                }
            });
        }else{
            
        }
    
  });
});
