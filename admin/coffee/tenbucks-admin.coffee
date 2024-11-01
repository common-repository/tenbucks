refresh = -> location.reload()
jQuery(document).ready ($) ->

    # iframe formating
    contentHeight = $('#adminmenuback').height()
    $iframe = $('#tenbucks-iframe');
    if $iframe.length
        iframeOffset = $iframe.offset()
        calculedHeight = contentHeight - iframeOffset.top - $('#wpfooter').height() - 20
        $iframe.animate height: calculedHeight

    # Registration form
    $('#tenbucks_register_form').submit (e) ->
        e.preventDefault();
        data = $(@).serializeArray()
        data.push name: 'action', value: 'tenbucks_create_key'

        notice = new Notice
        $('#submit'). attr 'disabled', true
        $.post ajaxurl, data, (res) ->
            if res.success
                notice.setType 'success'
                window.setTimeout(refresh, 2000) if res.data.needReload
            else
                notice.setType 'error'
                $('#submit').attr 'disabled', false
            notice.setMessage(res.data.message) if res.data.message

    class Notice
        constructor: (type = 'info', message, @parentSelector = '#notices', @ANIMATION_TIME = 400, scrollTo = false) ->
            @active = true
            @identifer = 'notice' + Date.now()
            message = $(@parentSelector).data('wait') if typeof message is 'undefined'
            $notice = $('<div />',
                       'class': "notice notice-#{type.replace(/^notice-/, '')}"
                       id: @identifer
                      )
                    .append $ '<p />', text: message

            $(@parentSelector).prepend($notice)

            #Scroll to the notice
            $('html, body').animate scrollTop: $notice.offset().top - 40, @ANIMATION_TIME if scrollTo

            return @

        getAlert: =>
            $(@parentSelector).find '#' + @identifer

        setType: (type) =>
            newClass = "notice notice-#{type.replace(/^notice-/, '')}"
            @getAlert().removeClass().addClass(newClass)
            @

        setMessage: (message) =>
            @getAlert().find('p').text(message)
            @

        setMessageHtml: (message) =>
            @getAlert().find('p').html(message)
            @

        hide: (callback) ->
            @getAlert().slideUp @ANIMATION_TIME, callback

        show: (callback) ->
            @getAlert().slideDown @ANIMATION_TIME, callback
