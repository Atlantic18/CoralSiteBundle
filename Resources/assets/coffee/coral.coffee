###*
 * Coral utilities
###
class CoralUtil
  verticalAlign: ->
    doVerticalAlign = (element)->
      divSize  = goog.style.getSize element
      if divSize.height < document.documentElement.clientHeight
        offset = Math.ceil divSize.height / 2 * -1
        element.style.marginTop = offset + "px"
        element.style.top = '50%'
      else
        element.style.marginTop = 0
        element.style.top = 0

    doVerticalAlign verticalAlign for verticalAlign in document.querySelectorAll ".vertical-align-wrap > div"

###*
 * AjaxForm
###
class AjaxForm
  constructor: (@dialog)->
    @formElement = null

  setForm: (form)=>
    @formElement = form
    $(@formElement).submit(@submit)

  onError: =>
    @dialog.getLogger().error "Unable to send form to: " + @formElement.action

  onSuccess: (data, textStatus, jqXHR)=>
    @dialog.unlock()
    @dialog.getLogger().info "Form successfully submitted."
    @dialog.setContent(data)

  submit: =>
    if(!@formElement)
      @dialog.getLogger().error "Form not set."
      return false

    target = @formElement.target
    @dialog.lock()

    jQuery.ajax
      data: $(@formElement).serialize(),
      url: @formElement.action,
      type: @formElement.method,
      error: @onError,
      success: @onSuccess

    false

###*
 * Abstract coral window
###
class ToasterMessage
  sort: ->
    counter = 0
    $(".coral-toaster").each ->
      $(this).animate({ 'bottom': ((counter++ * 8) + 1.5) + "em" })

  show: (message, type)->
    $("body").append "<div class='coral-toaster " + type + "'>" + message + "<a href='#' class='close'>&#215;</a></div>"
    sortFunction = @sort
    closeLink = $(".coral-toaster:last a.close")
    closeLink.click ()->
      $(this.parentNode).hide 500, ()->
        $(this).remove()
        sortFunction()
      false

    if type == 'info'
      closeLinkFunction = ()->
        closeLink.click()

      setTimeout closeLinkFunction, 5000

    @sort()

  info: (message)=>
    @show(message, "info")

  error: (message)=>
    @show(message, "error")

###*
 * Abstract coral window
###
class CoralWindow
  constructor: (@windowElement, @logger)->
    $(@windowElement).find("a.close-coral-modal, a.cancel.button").click @close
    $("a[rel=" + @windowElement.id + "]").click @open

  getLogger: =>
    return @logger

  close: =>
    @windowElement.className = @windowElement.className.replace " active", ""
    false

  open: (@onSuccess)=>
    @windowElement.className += " active"
    false

  setContent: (content)=>
    $(@windowElement).find(".content:first").html(content)

###*
 * Ajax coral window
###
class AjaxCoralWindow extends CoralWindow
  constructor: (@windowElement, @logger)->
    $(@windowElement).append "<section class='header'><h2>" + @windowElement.title + "</h2>" +
      '<a class="close-coral-modal">&#215;</a></section>' +
      '<section class="content"></section>' +
      '<section class="buttons"></section>'
    $(@windowElement).find("ul").appendTo($("#" + @windowElement.id + " section.buttons"));
    $(@windowElement)
      .find("ul").addClass("button-group radius right")
      .find("li a").addClass("button tiny")

    super @windowElement, @logger

  lock: =>
    $(@windowElement).find("section.content").append '<div class="overlay"><div class="ajax-loader">' +
      '<div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div>' +
      '</div></div>'

    scrollTop   = $("#" + @windowElement.id + " section.content").scrollTop()
    innerHeight = $("#" + @windowElement.id + " section.content").innerHeight()
    $(@windowElement).find("section.content .overlay")
      .css('top', scrollTop + 'px')
      .css('height', innerHeight + 'px')

  unlock: =>
    $(@windowElement).find("section.content .overlay").remove()

  close: =>
    @windowElement.className = @windowElement.className.replace " active", ""
    false

  load: (@onSuccess)=>
    @lock()
    $(@windowElement).find("section.content").load $(@windowElement).attr("rel"), @onSuccess

  open: (@onSuccess)=>
    @windowElement.className += " active"
    @load(@onSuccess)
    false


###*
 * File Upload coral window
###
class UploadCoralWindow extends AjaxCoralWindow
  constructor: (@windowElement, @logger)->
    @uploadDiv = $(".upload[rel=" + @windowElement.id + "]")
    @uploadUrl = $(@windowElement).attr "rel"
    if($(@uploadDiv).find("input").length > 0)
      @input = $(@uploadDiv).find("input").get(0);
      @input.ondrop = @onDrop
      @input.onchange = @onChange

    super @windowElement, @logger

  onDrop: (e)=>
    @readFile e.dataTransfer.files

  onChange: ()=>
    @readFile @input.files

  readFile: (files)=>
    if files.length == 1
      file = files[0]
      if !file.type.match(/image.*/)
        @logger.error "Uploaded file is not an image. Uploaded type: " + file.type
      else
        @uploadDiv.find("p").attr("original", @uploadDiv.find("p").text())
        @xhr = new XMLHttpRequest()
        @xhr.onprogress = @progress
        @xhr.onload = @onFinished
        @xhr.onerror = @onError
        @xhr.onabbort = @onAbort
        @xhr.open "POST", @uploadUrl
        @xhr.send file

  progress: (e)=>
    if e.lengthComputable
      percentage = Math.round (e.loaded * 100) / e.total
      @uploadDiv.find("p").text "Uploaded file: " + percentage + "%"

  onFinished: ()=>
    response = JSON.parse @xhr.responseText
    if @xhr.status == 201
      $(@windowElement).attr("rel", response.link)
      @logger.info "File uploaded successfully."
      @open null
    else
      @logger.error "Failed to upload file: [" + @xhr.status + "] " + response.message
    @resetButton()

  onError: (e)=>
    @logger.error "Failed to upload file" + e
    @resetButton()

  onAbort: (e)=>
    @logger.error "Upload canceled by the user."
    @resetButton()

  resetButton: ()=>
    @uploadDiv.find("p").text @uploadDiv.find("p").attr("original")

###*
 * Sitemap window
###
class SitemapWindow extends AjaxCoralWindow
  constructor: (@windowElement, @logger)->
    super @windowElement, @logger
    @ajaxForm = new AjaxForm(this)
    $(@windowElement).find(" .buttons .success").click(@ajaxForm.submit)

  moveNode: (item, container, _super)=>
    if $(item).prev("li").length == 1
      @lock()
      prevElementId = $(item).prev("li").find("a").attr("rel")
      url = $(item).find("a").attr("data-after").replace('/0', '/' + prevElementId)
      $(@windowElement).find("section.content").load(url, {}, @onLoad)
    if $(item).next("li").length == 1
      @lock()
      afterElementId = $(item).next("li").find("a").attr("rel")
      url = $(item).find("a").attr("data-before").replace('/0', '/' + afterElementId)
      $(@windowElement).find("section.content").load(url, {}, @onLoad)

  nodeAsLastChild: (event)=>
    liItem = event.target.parentNode.parentNode
    if $(liItem).prev("li").length == 1
      @lock()
      prevElementId = $(liItem).prev("li").find("a").attr("rel")
      url = $(event.target).attr("data-last-child-of").replace('/0', '/' + prevElementId)
      $(@windowElement).find("section.content").load(url, {}, @onLoad)

  listNode: (event)=>
    @lock()
    url = $(event.target).attr("data-url")
    $(@windowElement).find("section.content").load(url, @onLoad)

  addNode: (event)=>
    @lock()
    url = $(event.target).attr("data-url")
    $(@windowElement).find("section.content").load(url, {}, @onLoad)

  deleteNode: (event)=>
    if confirm "Are you sure?"
      @lock()
      url = $(event.target).attr("data-url")
      $(@windowElement).find("section.content").load(url, @onLoad)

  setContent: (content)=>
    super content
    @onLoad()

  onLoad: ()=>
    @makeSortable()
    @ajaxForm.setForm($(@windowElement).find("form").get(0))
    $(@windowElement).find("ul.sortable a").click ()->
      false
    $(@windowElement).find("ul.sortable [rel=last-child-of]").click @nodeAsLastChild
    $(@windowElement).find("ul.sortable [rel=edit]").click @listNode
    $(@windowElement).find("ul.sortable [rel=add]").click @addNode
    $(@windowElement).find("ul.sortable [rel=delete]").click @deleteNode

  makeSortable: ()=>
    $("#" + @windowElement.id + " ul.sortable > li > ul").sortable
      'nested': true
      'handle': 'i[rel=move]'
      'vertical': true
      'onDrop': @moveNode
    true

  open: ()->
    super @onLoad

###*
 * Coral Content Editor
###
class ContentEditor
  constructor: (@logger)->
    @editor =
      codemirror: null
      href: null
      activeWidget: null
      aloha: null
    @init()
    @initEditor()

  initEditor: ()->
    @editor.codemirror = CodeMirror document.body,
      lineNumbers: true
      theme: "monokai"
      mode: 'markdown'
      styleActiveLine: true
      matchBrackets: true
      continueComments: "Enter"
      extraKeys:
        "Enter": "newlineAndIndentContinueMarkdownList"
        "Esc": () =>
          if @editor.codemirror.doc.isClean()
            $(".coral-editor").hide()
          else
            @onExit()
      lineWrapping: true
      autofocus: true

    CodeMirror.commands.save = @saveEditor

    $(".CodeMirror").wrap("<div class=\"coral-editor\"></div>")
    $(".CodeMirror").after """
      <section class="buttons"><ul class="button-group radius right">
        <li><a href="#" class="cancel secondary button tiny"><i class="fi-x"></i> Discard changes</a></li>
        <li><a href="#" class="success button tiny"><i class="fi-check"></i> Save</a></li>
      </ul></section>
    """

    $(".coral-editor > .buttons a.cancel").click @discardChanges
    $(".coral-editor > .buttons a.success").click @saveEditor

    $(".coral-editor").hide()

  discardChanges: ()=>
    if @editor.codemirror.doc.isClean()
      $(".coral-editor").hide()
    else
      if confirm "All your changes will be lost!"
        $(".coral-editor").hide()

  onExit: ()=>
    @discardChanges()

  onSuccessSave: ()=>
    @editor.activeWidget.find("textarea").val(@editor.codemirror.getValue())
    @editor.activeWidget.find(".coral-content").html(markdown.toHTML(@editor.codemirror.getValue()))
    @editor.codemirror.doc.markClean()
    @logger.info "Text saved."

  saveEditor: ()=>
    $.ajax
      type: "POST"
      url: @editor.href
      data:
        content: @editor.codemirror.getValue()
        renderer: @editor.activeWidget.attr "coral-renderer"
      success: @onSuccessSave
      dataType: 'text'

  startEditor: (event) =>
    $(".coral-widget .coral-controls").hide()
    linkElement   = event.target
    widgetElement = $(linkElement).parents(".coral-widget").first()
    contentType   = widgetElement.attr "coral-renderer"

    if(contentType == "markdown" || contentType == "json")
      if $(widgetElement).find("textarea").length > 0
        @editor.activeWidget = widgetElement
        @editor.href = linkElement.href
        $(".coral-editor").show()
        @editor.codemirror.setValue($(widgetElement).find("textarea").val())

        if(contentType == "markdown")
          @editor.codemirror.setOption("mode", "markdown")

        if(contentType == "json")
          @editor.codemirror.setOption "mode",
            name: "javascript"
            json: true

        @editor.codemirror.doc.markClean()
        @editor.codemirror.focus()

    event.preventDefault()
    false

  init: ()->
    $(".coral-widget").click (e)->
      $(".coral-widget[id!=" + this.id + "] .coral-controls").hide()
      $(this).find(".coral-controls").toggle()

      $(this).find(".coral-controls").css('top', e.pageY - $(this).offset().top - 40)
    .dblclick ()->
      $(this).find(".coral-controls a[rel=edit]").click()

    $(".coral-widget .coral-content a")
      .click ()->
        false
      .dblclick ()->
        $(this).parents(".coral-widget").find(".coral-controls a[rel=edit]").click()
        false

    $(".coral-widget .coral-controls a[rel=edit]").click @startEditor
    $(".coral-widget .coral-controls a[rel=add]").click ()->
      false

    return

# ivolution.app.backend.start = (data) ->
#   ivolution.app.util.verticalAlign()
#   goog.events.listen window, goog.events.EventType.RESIZE, ivolution.app.util.verticalAlign


$(document).ready ->
  $(document).foundation()

  toaster = new ToasterMessage

  editor  = new ContentEditor(toaster)
  sitemapWindow = new SitemapWindow(document.getElementById("nodeSettings"), toaster)
  uploadWindow = new UploadCoralWindow(document.getElementById("uploadWindow"), toaster)

  $(document)
    .ajaxError (event, request, settings, exception)->
      if request.getResponseHeader("content-type") == "application/json"
        json = jQuery.parseJSON request.responseText
        toaster.error "Error: " + json.message + "<br />" + settings.url
      else
        toaster.error "Error " + exception + ":<br />" + settings.url
    .keyup (event)->
      if event.keyCode == 27
        $(".coral-modal.active a.close-coral-modal").click()

  $("a[confirm]").click ()->
    if(!confirm($(this).attr('confirm')))
      false

  true

Aloha.ready ->
  Aloha.jQuery('.coral-content').aloha()