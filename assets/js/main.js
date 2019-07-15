var twitterBrexit = (function ($) {
    'use strict'                               
    var $el,
        $controls,
        $controlsBottom,
        $alert,
        settings,
        autoupdate = 'off',
        callbacks = {
            onAutoupdate: function (data) {
                if (data.success && data.records > 0) {
                    $el.prepend(data.content);
                }
            },
            offAutoupdate: function (data) {
                var message = $alert.find('div').text();
                if (data.success && data.records > 0 && message == 0) {
                    $alert.hide().html(data.content);
                    $alert.fadeIn('slow');
                }
            },
            olderTweets: function (data) {
                if (data.success) {
                    if (data.records > 0) {
                        $el.append(data.content);    
                    }
                    
                    //there are no more tweets if less than 20
                    if (data.records < 20) {
                        $controlsBottom.find(settings.controlsAlert).html('');
                    }
                }
            }
        },
        _preparePromiseData = function (auto, tweet, url) {
            var tweetId = $el.find('li:' + tweet).data('tweetId'),
                data = {
                    url: url,
                    method:'get',
                    data: {
                        tweetId: 0,
                        autoupdate: auto
                    }
                };
            if (!tweetId || (tweetId && tweetId.length === 0) ) {
                data.data.type = 'new';
            } else {
                data.data.tweetId = tweetId;
            }
            return data;
        },
        _promiseCallback = function (data, callback, callbackThen) {
            var promise = $.ajax(data);
            promise.done(callback);
            if (callbackThen && typeof callbackThen === 'function') {
                promise.then(callbackThen);
            }
        },
        _automatedOnOffHandler = function (event) {
            var $checkbox = (event ? $(this) : $controls.find('[type="checkbox"]'));
            autoupdate = ($checkbox.is(':checked') ? 'on' : 'off');
        },
        _loadOldTweetsOnClickHandler = function () {
            _promiseCallback(_preparePromiseData('on', 'last-child', 'ajax/get-older-tweets.php'), callbacks.olderTweets);
        },
        _loadNewTweetsOnClickHandler = function () {
            $alert.html('');
            _promiseCallback(_preparePromiseData('on', 'first-child', 'ajax/get-tweets.php'), callbacks.onAutoupdate);
        },
        _loadNewTweetsOnControlsHandler = function () {         
            _promiseCallback(_preparePromiseData(autoupdate, 'first-child', 'ajax/get-tweets.php'), callbacks[autoupdate + 'Autoupdate']);
        },
        init = function ($container, $feedCntr) {
            settings = $container.data();
            $el = $container.find(settings.tweets);
            $controls = $container.find(settings.topControls);
            $controlsBottom = $container.find(settings.bottomControls);
            $alert = $controls.find(settings.controlsAlert);

            $alert.on('click', _loadNewTweetsOnClickHandler);
            $controls.find('[type="checkbox"]').on('change', _automatedOnOffHandler);
            $controlsBottom.on('click', _loadOldTweetsOnClickHandler);

            _automatedOnOffHandler();
            setInterval(_loadNewTweetsOnControlsHandler, 10000);
        };
        
    return {
        init: init
    }
}(jQuery));

$(document).ready(function () {
    twitterBrexit.init($('#brexit-tweet-app'));
});