<?php
/*
 * Title:	 Default Commentary Color Selection
 * Date:	Sep 06, 2004
 * Version:	1.11
 * Author:	Joshua Ecklund
 * Email:	m.prowler@cox.net
 * Purpose:	Allow user to select a default color for their
 *		commentary and/or emote text.
 *
 * --Change Log--
 *
 * Date:	Sep 06, 2004
 * Version:	1.0
 * Purpose:	Initial Release
 *
 * Date:	Sep 06, 2004
 * Version:	1.1
 * Purpose:	Fixed emote bug, emotes are now colored just like
 *		other commentary.
 *
 * Date:	Sep 06, 2004
 * Version:	1.11
 * Purpose:	Changed to allow each user to set both commentary
 *		color and emote color for themselves.
 *
 * * Version:	2.0 by WK
 * Added JavaScript to allow colors in preview and to fix character count.
 * Added game master commentary support.
 * Cleaned up white space. 
 * Note: Core bugs not fixed:
 * 1. Core trims the commentary. JavaScript preview does not. Hence, white space
 * before emotes causes incorrect preview.
 * 2. Core splits long words in comments by adding spaces. JavaScript preview
 * disregards this and thus may show incorrect number of characters left.   
 */

function defaultcolor_getmoduleinfo()
{
	$gm_pref_array = array();
	if( defined( 'SU_IS_GAMEMASTER' ) )
	{
		global $session;
		if( $session['user']['superuser'] & SU_IS_GAMEMASTER )
			$gm_pref_array = array(
				'user_gamemaster'=>'Default color for game master commentary,|',
				'Only game masters are presented with the game master preference.,note'
			);
	}
	$info = array(
		'name'=>'Default Commentary Color Selection',
		'version'=>'2.0',
		'author'=>'Joshua Ecklund, modified by `&W`7hite `&K`7night`7, modification by `i`b`&Xpert`b`i',
		'category'=>'General',
		'download'=>'http://dragonprime.net/',
		'prefs'=>array_merge(
			array(
				'Default Colors,title',
				'user_color'=>'Default color code for commentary,|',
				'user_emote'=>'Default color code for emotes,|',
				'user_yomcolor'=>'Default color code for mailing,|',
			),
			$gm_pref_array
		)
	);
	return $info;
}

function defaultcolor_install()
{
	module_addhook( 'commentary' );
	module_addhook( 'insertcomment' );
}

function defaultcolor_uninstall(){}

function defaultcolor_isvalid( $color_code )
{
	global $nestedtags;
	$old_nesting = $nestedtags;
	$validity = false;
	if( strlen( $color_code ) === 2
			 && $color_code{0} === '`'
			 && $color_code{1} !== 'i'
			 && $color_code{1} !== 'b'
			 && color_sanitize( $color_code ) === ''
			 && appoencode( $color_code ) !== $color_code )
		$validity = true;
	$nestedtags = $old_nesting;
	return $validity;
}

function defaultcolor_dohook( $hook, $args ) {
	$sql = "SELECT character_maximum_length    
			FROM information_schema.columns  
			WHERE table_name = 'commentary' AND column_name = 'comment'";
	$res = db_query($sql);
	$row = db_fetch_assoc($res);
	$commentary_length = $row['character_maximum_length'];
	
	$color = trim( get_module_pref( 'user_color' ) );
	$emote = trim( get_module_pref( 'user_emote' ) );
	$gm_color = '';
	$gm_color_isvalid = false;

	global $session;
	$is_gm = ( defined( 'SU_IS_GAMEMASTER' )
		&& $session['user']['superuser'] & SU_IS_GAMEMASTER );
	if( $is_gm )
	{
		$gm_color = trim( get_module_pref( 'user_gamemaster' ) );
		$gm_color_isvalid = defaultcolor_isvalid( $gm_color );
	}

	if( $hook === 'commentary' )
	{
		$comment = $args['commentline'];
		// check if this is an emote line
		$is_emote = ( $comment{0} === ':'
				|| strpos( $comment, '/me' ) === 0
				|| $is_gm && strpos( $comment, '/game' ) === 0 );
		if( $is_emote )
		{
			if( $is_gm && strpos( $comment, '/game' ) === 0 )
			{
				if( $gm_color_isvalid )
					$args['commentline'] = '/game'.$gm_color.substr( $comment, 5 );
					// 5 is the length of the string "/game"
			}
			elseif( defaultcolor_isvalid( $emote ) )
				$args['commentline'] = preg_replace( '!^(:{1,2}|\/me)!', '${1}'.$emote, $comment );
		}
		elseif( defaultcolor_isvalid( $color ) )
			$args['commentline'] = $color.$comment;
	}
	elseif( $hook === 'insertcomment' )
	{
		global $nestedtags;
		$old_nesting = $nestedtags;
		$color_isvalid = defaultcolor_isvalid( $color );
		$emote_isvalid = defaultcolor_isvalid( $emote );
		if( $color_isvalid || $emote_isvalid || $gm_color_isvalid )
		{
			/* Sorry, the JS is ugly and fragile. This really should be done in the core. */

			$name_len = strlen( appoencode( '`&'.$session['user']['name'] ) );
			$replace_comment = 'colLtCyan';
			$color_js_fix1 = $color_js_fix2 = '';
			if( $color_isvalid )
			{
				$replace_comment = preg_replace( '/^.*[\'"](.*)[\'"].*$/', '${1}', appoencode( $color ) );
				$color_js_fix1 = '-0;
					document.getElementById("inputinsertcommentary").maxLength=count';
				$color_js_fix2 = "\n\t\t".'if(this.maxLength=='.$commentary_length.') this.maxLength='.$commentary_length.';';
			}
	
			$script = '
			<script type="text/javascript"><!--
				function colorFilter(){
					var input=document.getElementById("inputinsertcommentary");
					var inputVal=input.value;
					var preview=document.getElementById("previewtextinsertcommentary");
					var previewText=preview.innerHTML;
					var startAt=previewText.indexOf(", ");
					var strStart=previewText.substring(0,startAt);
					var strEnd=previewText.substring(startAt);
					var oldColor="colLtCyan";
					var newColor="'.$replace_comment.'";'."\n";

					/* Game master's "/game" gets special treatment: there is no name. */
					$gm_js_check = '';
					$gm_js_code = '';
					if( $gm_color_isvalid )
					{
						$replace_game = preg_replace( '/^.*[\'"](.*)[\'"].*$/', '${1}', appoencode( $gm_color ) );
						$gm_js_check = '||inputVal.substring(0,5)=="/game"';
						$gm_js_code = "\t\t".'if(inputVal.substring(0,5)=="/game"){
							nameLen=0;
							newColor="'.$replace_game.'";
						}else'."\n\t";
					}

					/* Emotes... */
					$emote_js_check = '';
					$replace_emote = 'colLtWhite';
					if( $emote_isvalid )
					{
						$replace_emote = preg_replace( '/^.*[\'"](.*)[\'"].*$/', '${1}', appoencode( $emote ) );
						$emote_js_check = 'inputVal.substring(0,1)==":"||inputVal.substring(0,3)=="/me"';
						/* No need to check for "::" because ":" covers that case for the script. */
					}
			
					if( !empty( $emote_js_check ) || !empty( $gm_js_check ) )
					{
						$script.= '	if('.$emote_js_check.$gm_js_check.'){
							var nameLen='.$name_len.';
							oldColor="colLtWhite";'
									."\n".$gm_js_code
									.'		newColor="'.$replace_emote.'";
							strStart=previewText.substring(0,nameLen);
							strEnd=previewText.substring(nameLen);
							var oldLen='.$commentary_length.'-inputVal.length;
							var newLen=oldLen-0;
							var charsLeft=document.getElementById("charsleftinsertcommentary");
							var newHTML=charsLeft.innerHTML.replace(oldLen,newLen);
							if(newLen<0) newHTML=\'<span class="colLtRed">\'+newHTML+"<\/"+"span>";
							charsLeft.innerHTML=newHTML;
						}'."\n";
					}

					rawoutput( $script
						.'	preview.innerHTML=strStart+strEnd.replace(oldColor,newColor);
				}
				
				function fixCounter(){
					var count=document.getElementById("inputinsertcommentary").maxLength'
					.$color_js_fix1.';
					document.getElementById("inputinsertcommentary").onkeyup=function(){
						previewtextinsertcommentary(this.value, count);'
						.$color_js_fix2.'
						colorFilter();
					};
					document.onkeydown=null;
				}
				
				document.onkeydown=fixCounter;
			//--></script>' );
		}
		$nestedtags = $old_nesting;
	}
	return $args;
}
?>