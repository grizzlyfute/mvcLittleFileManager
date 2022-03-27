<?php
$title = $tr->translate('file.edit');
$path = $VIEWVARS['file']->getFullPath();
include ('views/headers.php'); ?>
<?php
$file_url = $_SERVER['PHP_SELF'] . '?action=download&p=' . rawurlencode($path);
?>

<script>
//Save file
function edit_save(e, t)
{
	var n = "ace" == t ? editor.getSession().getValue() : document.getElementById("normal-editor").value;
	if (n)
	{
		var a = document.createElement("form");
		a.setAttribute("method", "POST"), a.setAttribute("action", "");
		var o = document.createElement("textarea");
		o.setAttribute("type", "textarea"), o.setAttribute("name", "savedata");
		var c = document.createTextNode(n);
		o.appendChild(c), a.appendChild(o), document.body.appendChild(a), a.submit()
	}
}
</script>
	<div class="path">
		<div class="row">
			<div class="col-xs-12 col-sm-5 col-lg-6 pt-1">
				<div class="btn-toolbar" role="toolbar">
					<?php if (!$VIEWVARS['isnormaleditor']): ?>
						<div class="btn-group js-ace-toolbar">
							<button data-cmd="none"
									data-option="fullscreen"
									class="btn btn-sm btn-outline-secondary"
									id="js-ace-fullscreen"
									title="Fullscreen">
								<i class="fas fa-expand" title="Fullscreen"></i>
							</button>
							<button data-cmd="find"
									class="btn btn-sm btn-outline-secondary"
									id="js-ace-search"
									title="<?php $tr->trans('file.search')?>">
								<i class="fas fa-search" title="Search"></i>
							</button>
							<button data-cmd="undo"
									class="btn btn-sm btn-outline-secondary"
									id="js-ace-undo"
									title="Undo">
								<i class="fas fa-undo" title="Undo"></i>
							</button>
							<button data-cmd="redo"
									class="btn btn-sm btn-outline-secondary"
									id="js-ace-redo"
									title="Redo">
								<i class="fas fa-redo" title="Redo"></i></button>
							<button data-cmd="none"
									data-option="wrap"
									class="btn btn-sm btn-outline-secondary"
									id="js-ace-wordWrap"
									title="Word Wrap">
								<i class="fas fa-paragraph" title="Word Wrap"></i>
							</button>
							<button data-cmd="none"
									data-option="help"
									class="btn btn-sm btn-outline-secondary"
									id="js-ace-help"
									title="<?php $tr->trans('common.help'); ?>">
									<i class="fas fa-question" title="<?php $tr->trans('common.help'); ?>"></i>
							</button>
							<select id="js-ace-mode"
									data-type="mode"
									title="Select Document Type"
									class="btn-outline-secondary border-left-0 d-none d-md-block">
								<option>-- Select Mode --</option>
									<option value="ace/mode/abap">abap</option>
									<option value="ace/mode/abc">abc</option>
									<option value="ace/mode/actionscript">actionscript</option>
									<option value="ace/mode/ada">ada</option>
									<option value="ace/mode/apache_conf">apache_conf</option>
									<option value="ace/mode/apex">apex</option>
									<option value="ace/mode/applescript">applescript</option>
									<option value="ace/mode/aql">aql</option>
									<option value="ace/mode/asciidoc">asciidoc</option>
									<option value="ace/mode/asl">asl</option>
									<option value="ace/mode/assembly_x86">assembly_x86</option>
									<option value="ace/mode/autohotkey">autohotkey</option>
									<option value="ace/mode/batchfile">batchfile</option>
									<option value="ace/mode/bro">bro</option>
									<option value="ace/mode/c9search">c9search</option>
									<option value="ace/mode/c_cpp">c_cpp</option>
									<option value="ace/mode/cirru">cirru</option>
									<option value="ace/mode/clojure">clojure</option>
									<option value="ace/mode/cobol">cobol</option>
									<option value="ace/mode/coffee">coffee</option>
									<option value="ace/mode/coldfusion">coldfusion</option>
									<option value="ace/mode/crystal">crystal</option>
									<option value="ace/mode/csharp">csharp</option>
									<option value="ace/mode/csound_document">csound_document</option>
									<option value="ace/mode/csound_orchestra">csound_orchestra</option>
									<option value="ace/mode/csound_score">csound_score</option>
									<option value="ace/mode/csp">csp</option>
									<option value="ace/mode/css">css</option>
									<option value="ace/mode/curly">curly</option>
									<option value="ace/mode/d">d</option>
									<option value="ace/mode/dart">dart</option>
									<option value="ace/mode/diff">diff</option>
									<option value="ace/mode/django">django</option>
									<option value="ace/mode/dockerfile">dockerfile</option>
									<option value="ace/mode/dot">dot</option>
									<option value="ace/mode/drools">drools</option>
									<option value="ace/mode/edifact">edifact</option>
									<option value="ace/mode/eiffel">eiffel</option>
									<option value="ace/mode/ejs">ejs</option>
									<option value="ace/mode/elixir">elixir</option>
									<option value="ace/mode/elm">elm</option>
									<option value="ace/mode/erlang">erlang</option>
									<option value="ace/mode/forth">forth</option>
									<option value="ace/mode/fortran">fortran</option>
									<option value="ace/mode/fsharp">fsharp</option>
									<option value="ace/mode/fsl">fsl</option>
									<option value="ace/mode/ftl">ftl</option>
									<option value="ace/mode/gcode">gcode</option>
									<option value="ace/mode/gherkin">gherkin</option>
									<option value="ace/mode/gitignore">gitignore</option>
									<option value="ace/mode/glsl">glsl</option>
									<option value="ace/mode/gobstones">gobstones</option>
									<option value="ace/mode/golang">golang</option>
									<option value="ace/mode/graphqlschema">graphqlschema</option>
									<option value="ace/mode/groovy">groovy</option>
									<option value="ace/mode/haml">haml</option>
									<option value="ace/mode/handlebars">handlebars</option>
									<option value="ace/mode/haskell">haskell</option>
									<option value="ace/mode/haskell_cabal">haskell_cabal</option>
									<option value="ace/mode/haxe">haxe</option>
									<option value="ace/mode/hjson">hjson</option>
									<option value="ace/mode/html">html</option>
									<option value="ace/mode/html_elixir">html_elixir</option>
									<option value="ace/mode/html_ruby">html_ruby</option>
									<option value="ace/mode/ini">ini</option>
									<option value="ace/mode/io">io</option>
									<option value="ace/mode/jack">jack</option>
									<option value="ace/mode/jade">jade</option>
									<option value="ace/mode/java">java</option>
									<option value="ace/mode/javascript">javascript</option>
									<option value="ace/mode/json">json</option>
									<option value="ace/mode/jsoniq">jsoniq</option>
									<option value="ace/mode/jsp">jsp</option>
									<option value="ace/mode/jssm">jssm</option>
									<option value="ace/mode/jsx">jsx</option>
									<option value="ace/mode/julia">julia</option>
									<option value="ace/mode/kotlin">kotlin</option>
									<option value="ace/mode/latex">latex</option>
									<option value="ace/mode/less">less</option>
									<option value="ace/mode/liquid">liquid</option>
									<option value="ace/mode/lisp">lisp</option>
									<option value="ace/mode/livescript">livescript</option>
									<option value="ace/mode/logiql">logiql</option>
									<option value="ace/mode/logtalk">logtalk</option>
									<option value="ace/mode/lsl">lsl</option>
									<option value="ace/mode/lua">lua</option>
									<option value="ace/mode/luapage">luapage</option>
									<option value="ace/mode/lucene">lucene</option>
									<option value="ace/mode/makefile">makefile</option>
									<option value="ace/mode/markdown">markdown</option>
									<option value="ace/mode/mask">mask</option>
									<option value="ace/mode/matlab">matlab</option>
									<option value="ace/mode/maze">maze</option>
									<option value="ace/mode/mel">mel</option>
									<option value="ace/mode/mixal">mixal</option>
									<option value="ace/mode/mushcode">mushcode</option>
									<option value="ace/mode/mysql">mysql</option>
									<option value="ace/mode/nginx">nginx</option>
									<option value="ace/mode/nim">nim</option>
									<option value="ace/mode/nix">nix</option>
									<option value="ace/mode/nsis">nsis</option>
									<option value="ace/mode/objectivec">objectivec</option>
									<option value="ace/mode/ocaml">ocaml</option>
									<option value="ace/mode/pascal">pascal</option>
									<option value="ace/mode/perl">perl</option>
									<option value="ace/mode/perl6">perl6</option>
									<option value="ace/mode/pgsql">pgsql</option>
									<option value="ace/mode/php">php</option>
									<option value="ace/mode/php_laravel_blade">php_laravel_blade</option>
									<option value="ace/mode/pig">pig</option>
									<option value="ace/mode/plain_text">plain_text</option>
									<option value="ace/mode/powershell">powershell</option>
									<option value="ace/mode/praat">praat</option>
									<option value="ace/mode/prolog">prolog</option>
									<option value="ace/mode/properties">properties</option>
									<option value="ace/mode/protobuf">protobuf</option>
									<option value="ace/mode/puppet">puppet</option>
									<option value="ace/mode/python">python</option>
									<option value="ace/mode/r">r</option>
									<option value="ace/mode/razor">razor</option>
									<option value="ace/mode/rdoc">rdoc</option>
									<option value="ace/mode/red">red</option>
									<option value="ace/mode/redshift">redshift</option>
									<option value="ace/mode/rhtml">rhtml</option>
									<option value="ace/mode/rst">rst</option>
									<option value="ace/mode/ruby">ruby</option>
									<option value="ace/mode/rust">rust</option>
									<option value="ace/mode/sass">sass</option>
									<option value="ace/mode/scad">scad</option>
									<option value="ace/mode/scala">scala</option>
									<option value="ace/mode/scheme">scheme</option>
									<option value="ace/mode/scss">scss</option>
									<option value="ace/mode/sh">sh</option>
									<option value="ace/mode/sjs">sjs</option>
									<option value="ace/mode/slim">slim</option>
									<option value="ace/mode/smarty">smarty</option>
									<option value="ace/mode/snippets">snippets</option>
									<option value="ace/mode/soy_template">soy_template</option>
									<option value="ace/mode/space">space</option>
									<option value="ace/mode/sparql">sparql</option>
									<option value="ace/mode/sql">sql</option>
									<option value="ace/mode/sqlserver">sqlserver</option>
									<option value="ace/mode/stylus">stylus</option>
									<option value="ace/mode/svg">svg</option>
									<option value="ace/mode/swift">swift</option>
									<option value="ace/mode/tcl">tcl</option>
									<option value="ace/mode/terraform">terraform</option>
									<option value="ace/mode/tex">tex</option>
									<option value="ace/mode/text">text</option>
									<option value="ace/mode/textile">textile</option>
									<option value="ace/mode/toml">toml</option>
									<option value="ace/mode/tsx">tsx</option>
									<option value="ace/mode/turtle">turtle</option>
									<option value="ace/mode/twig">twig</option>
									<option value="ace/mode/typescript">typescript</option>
									<option value="ace/mode/vala">vala</option>
									<option value="ace/mode/vbscript">vbscript</option>
									<option value="ace/mode/velocity">velocity</option>
									<option value="ace/mode/verilog">verilog</option>
									<option value="ace/mode/vhdl">vhdl</option>
									<option value="ace/mode/visualforce">visualforce</option>
									<option value="ace/mode/wollok">wollok</option>
									<option value="ace/mode/xml">xml</option>
									<option value="ace/mode/xquery">xquery</option>
									<option value="ace/mode/yaml">yaml</option>
							</select>
							<select id="js-ace-theme"
									data-type="theme"
									title="Select Theme"
									class="btn-outline-secondary border-left-0 d-none d-lg-block">
								<option>-- Select Theme --</option>
									<option value="ace/theme/ambiance">ambiance</option>
									<option value="ace/theme/chaos">chaos</option>
									<option value="ace/theme/chrome">chrome</option>
									<option value="ace/theme/clouds">clouds</option>
									<option value="ace/theme/clouds_midnight">clouds_midnight</option>
									<option value="ace/theme/cobalt">cobalt</option>
									<option value="ace/theme/crimson_editor">crimson_editor</option>
									<option value="ace/theme/dawn">dawn</option>
									<option value="ace/theme/dracula">dracula</option>
									<option value="ace/theme/dreamweaver">dreamweaver</option>
									<option value="ace/theme/eclipse">eclipse</option>
									<option value="ace/theme/github">github</option>
									<option value="ace/theme/gob">gob</option>
									<option value="ace/theme/gruvbox">gruvbox</option>
									<option value="ace/theme/idle_fingers">idle_fingers</option>
									<option value="ace/theme/iplastic">iplastic</option>
									<option value="ace/theme/katzenmilch">katzenmilch</option>
									<option value="ace/theme/kr_theme">kr_theme</option>
									<option value="ace/theme/kuroir">kuroir</option>
									<option value="ace/theme/merbivore">merbivore</option>
									<option value="ace/theme/merbivore_soft">merbivore_soft</option>
									<option value="ace/theme/mono_industrial">mono_industrial</option>
									<option value="ace/theme/monokai">monokai</option>
									<option value="ace/theme/pastel_on_dark">pastel_on_dark</option>
									<option value="ace/theme/solarized_dark">solarized_dark</option>
									<option value="ace/theme/solarized_light">solarized_light</option>
									<option value="ace/theme/sqlserver">sqlserver</option>
									<option value="ace/theme/terminal">terminal</option>
									<option value="ace/theme/textmate">textmate</option>
									<option value="ace/theme/tomorrow">tomorrow</option>
									<option value="ace/theme/tomorrow_night">tomorrow_night</option>
									<option value="ace/theme/tomorrow_night_blue">tomorrow_night_blue</option>
									<option value="ace/theme/tomorrow_night_bright">tomorrow_night_bright</option>
									<option value="ace/theme/tomorrow_night_eighties">tomorrow_night_eighties</option>
									<option value="ace/theme/twilight">twilight</option>
									<option value="ace/theme/vibrant_ink">vibrant_ink</option>
									<option value="ace/theme/xcode">xcode</option>
							</select>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<div class="edit-file-actions col-xs-12 col-sm-7 col-lg-6 text-right pt-1">
				<a title="Back"
				   class="btn btn-sm btn-outline-primary"
				   href="?action=view&p=<?php echo rawurlencode($VIEWVARS['file']->getFullPath()) ?>">
					<i class="fas fa-reply"></i>&nbsp;<?php $tr->trans('common.back'); ?>
				</a>
				<?php if ($VIEWVARS['istext']): ?>
					<?php if ($VIEWVARS['isnormaleditor']): ?>
						<a title="Advanced"
							class="btn btn-sm btn-outline-primary"
							href="?action=edit&p=<?php echo rawurlencode($VIEWVARS['file']->getFullPath()) . '&env=ace'?>">
							<i class="fas fa-edit"></i>&nbsp;<?php $tr->trans('file.advancededitor'); ?>
						</a>
						<button type="button"
								class="btn btn-sm btn-outline-primary"
								name="Save"
								data-url="<?php echo rawurlencode($file_url) ?>" onclick="edit_save(this,'nrl')">
								<i class="fas fa-save"></i>&nbsp;<?php $tr->trans('file.save'); ?>
						</button>
					<?php else: ?>
						<a title="Plain Editor"
							class="btn btn-sm btn-outline-primary"
							href="?action=edit&p=<?php echo rawurlencode($VIEWVARS['file']->getFullPath()) ?>">
							<i class="far fa-edit"></i>&nbsp;<?php echo $tr->trans('file.simpleeditor'); ?>
						</a>
						<button type="button"
								class="btn btn-sm btn-outline-primary"
								name="Save" data-url="<?php echo rawurlencode($file_url) ?>" onclick="edit_save(this,'ace')">
							<i class="fas fa-save"></i>&nbsp;<?php $tr->trans('file.save');  ?>
						</button>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
		<?php if ($VIEWVARS['istext'] && $VIEWVARS['isnormaleditor']): ?>
		<textarea class="mt-2" id="normal-editor" rows="33" cols="120" style="width: 99.5%;"><?php echo htmlspecialchars($VIEWVARS['content']) ?></textarea>
		<?php elseif ($VIEWVARS['istext']): ?>
		<div class="mt-2" id="editor" contenteditable="true"><?php echo htmlspecialchars($VIEWVARS['content']) ?></div>
		<?php else: ?>
		<span>
			Not supported file
		</span>
		<?php endif; ?>
	</div>

<?php if (!$VIEWVARS['isnormaleditor']): ?>
<script src="libs/ace-1.4.12/ace.js"></script>
<script>
var editor = ace.edit("editor");
editor.getSession().setMode("ace/mode/javascript");
//editor.setTheme("ace/theme/twilight"); //Dark Theme
function ace_commend(cmd)
{
	editor.commands.exec(cmd, editor);
}
editor.commands.addCommands(
[{
	name: 'save', bindKey: {win: 'Ctrl-S',  mac: 'Command-S'},
	exec: function(editor) { edit_save(this, 'ace'); }
}]);

$(function()
{
	$(".js-ace-toolbar").on("click", 'button', function(e)
	{
		e.preventDefault();
		let cmdValue = $(this).attr("data-cmd"), editorOption = $(this).attr("data-option");
		if (cmdValue && cmdValue != "none")
		{
			ace_commend(cmdValue);
		}
		else if (editorOption)
		{
			if (editorOption == "fullscreen")
			{
				// $$$$ just unreadble - but works
				(
					void 0 !== document.fullScreenElement && null === document.fullScreenElement ||
					void 0 !== document.msFullscreenElement && null === document.msFullscreenElement ||
					void 0 !== document.mozFullScreen && !document.mozFullScreen ||
					void 0 !== document.webkitIsFullScreen && !document.webkitIsFullScreen
				) &&
				(
					editor.container.requestFullScreen ?
					editor.container.requestFullScreen() :
						editor.container.mozRequestFullScreen ?
						editor.container.mozRequestFullScreen() :
						editor.container.webkitRequestFullScreen ?
						editor.container.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT) :
							editor.container.msRequestFullscreen && editor.container.msRequestFullscreen()
				);
			}
			else if (editorOption == "wrap")
			{
				let wrapStatus = (editor.getSession().getUseWrapMode()) ? false : true;
				editor.getSession().setUseWrapMode(wrapStatus);
			}
			else if (editorOption == "help")
			{
				var helpHtml =
					"<li>Save (Ctrl + S)</li>\n" +
					"<li>Find (Ctrl + F)</li>\n" +
					"<li>Undo (Ctrl + Z)</li>\n" +
					"<li>Redo (Ctrl + Y)</li>\n" +
					"<li>Go to Line (Ctrl + L)</li>\n" +
					"<li><a href='https://github.com/ajaxorg/ace/wiki/Default-Keyboard-Shortcuts' target='_blank'>More Shortcuts</a></li>\n" +
					"";
				var tplObj=
				{
					id:1028,
					title:"<?php $tr->trans('common.help'); ?>",
					action:false,
					content:helpHtml
				},
				tpl=$("#js-tpl-modal").html();
				$('#wrapper').append(template(tpl,tplObj));
				$("#js-ModalCenter-1028").modal('show');
			}
		}
	});
	$("select#js-ace-mode, select#js-ace-theme").on("change", function(e)
	{
		e.preventDefault();
		let selectedValue = $(this).val(),
			selectionType = $(this).attr("data-type");
		if(selectedValue && selectionType == "mode")
		{
			editor.getSession().setMode(selectedValue);
		}
		else if(selectedValue && selectionType == "theme")
		{
			editor.setTheme(selectedValue);
		}
	});
});
</script>
<?php endif; ?>
<?php include ('views/footers.php'); ?>
