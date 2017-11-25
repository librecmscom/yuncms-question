window.yii.question = (function ($) {
    var pub = {
        // whether this module is currently active. If false, init() will not be called for this module
        // it will also not be called for all its child modules. If this property is undefined, it means true.
        isActive: true,
        init: function () {
            console.info('init question.');
            $(".question-comment-btn").click(function () {
                var model_id = $(this).data('model_id');
                var to_user_id = $(this).data('to_user_id');
                var content = $("#comment-" + "content-" + model_id).val();
                pub.add_comment(model_id, content, to_user_id);
                $("#comment-content-" + model_id + "").val('');
            });

            /*采纳回答为最佳答案*/
            $(".adopt-answer").click(function () {
                var answer_id = jQuery(this).data('answer_id');
                jQuery("#adoptAnswerSubmit").attr('data-answer_id', answer_id);
                jQuery("#answer_quote").html(jQuery(this).data('answer_content'));
            });

            $("#adoptAnswerSubmit").click(function () {
                jQuery.post("/question/answer/adopt", {answerId: jQuery(this).data('answer_id')});
            });

            $(".widget-comments").on('show.bs.collapse', function () {
                pub.load_comments($(this).data('model_id'));
            });

            $(".widget-comments").on('hide.bs.collapse', function () {
                pub.clear_comments($(this).data('model_id'));
            });

            pub.handleCollect();
            pub.handleFollow();
        },

        /**
         * 发布评论
         * @param model_id
         * @param content
         * @param to_user_id
         */
        add_comment: function (model_id, content, to_user_id) {
            var postData = {model_id: model_id, content: content};
            if (to_user_id > 0) {
                postData.to_user_id = to_user_id;
            }
            $.post('/question/comment/create', postData, function (html) {
                $("#comments-" + model_id + " .widget-comment-list").append(html);
                $("#comment-" + "content-" + model_id).val('');
            });
        },

        /**
         * 清除评论
         * @param id
         */
        clear_comments: function (id) {
            $("#comments-" + id + " .widget-comment-list").empty();
        },

        load_comments: function (id) {
            $.get('/question/comment/index', {id: id}, function (html) {
                $("#comments-" + id + " .widget-comment-list").append(html);
            });
        },

        handleCollect: function () {
            $(document).on('click', '[data-target="question-collect"]', function (e) {
                $(this).button('loading');
                var collect_btn = $(this);
                var model_id = $(this).data('model_id');
                var show_num = $(this).data('show_num');
                $.post("/question/question/collection", {model_id: model_id}, function (result) {
                    collect_btn.removeClass('disabled');
                    collect_btn.removeAttr('disabled');
                    if (result.status === 'collected') {
                        collect_btn.html('已收藏');
                        collect_btn.addClass('active');
                    } else {
                        collect_btn.html('收藏');
                        collect_btn.removeClass('active');
                    }

                    /*是否操作收藏数*/
                    if (Boolean(show_num)) {
                        var collect_num = collect_btn.nextAll("#collection-num").html();
                        if (result.status === 'collected') {
                            collect_btn.nextAll("#collection-num").html(parseInt(collect_num) + 1);
                        } else {
                            collect_btn.nextAll("#collection-num").html(parseInt(collect_num) - 1);
                        }
                    }
                });
            });
        },

        /**
         * 关注问题
         */
        handleFollow: function () {
            $(document).on('click', '[data-target="question-follow"]', function (e) {
                $(this).button('loading');
                var follow_btn = $(this);
                var model_id = $(this).data('model_id');
                var show_num = $(this).data('show_num');
                $.post("/question/question/attention", {model_id: model_id}, function (result) {
                    follow_btn.removeClass('disabled');
                    follow_btn.removeAttr('disabled');
                    if (result.status == 'followed') {
                        follow_btn.html('已关注');
                        follow_btn.addClass('active');
                    } else {
                        follow_btn.html('关注');
                        follow_btn.removeClass('active');
                    }

                    /*是否操作关注数*/
                    if (Boolean(show_num)) {
                        var follower_num = follow_btn.nextAll("#follower-num").html();
                        if (result.status == 'followed') {
                            follow_btn.nextAll("#follower-num").html(parseInt(follower_num) + 1);
                        } else {
                            follow_btn.nextAll("#follower-num").html(parseInt(follower_num) - 1);
                        }
                    }
                    return callback(result.status);
                });
            });
        }
    };
    return pub;
})(window.jQuery);