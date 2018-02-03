<?php

function defaultcolor_getmoduleinfo()
{
    return [
        'name' => 'Default Chat Colors',
        'version' => '2.0',
        'author' => 'MarcTheSlayer and Stephen Kise',
        'category' => 'General',
        'download' => 'http://dragonprime.net/',
        'prefs' => [
            'Default Chat Colors,title',
            'user_color' => 'Default color code for commentary,|',
            'user_emote' => 'Default color code for emotes,|',
            'user_yomcolor' => 'Default color code for mailing,|',
        ]
    ];
    return $info;
}

function defaultcolor_install()
{
    module_addhook('chat-intercept');
    module_addhook('insertcomment');
    return true;
}

function defaultcolor_uninstall()
{
    return true;
}

function defaultcolor_isvalid($color)
{
    $validity = false;
    if(strlen($color) === 2) {
        $validity = true;
    }
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

    switch ($hook) {
        case 'chat-intercept':
            if (array_key_exists('edited', $args)) {
                return $args;
            }
            $comment = $args['comment'];
            $isEmote = false;
            $emotes = [':', '::', '/me', '/npc'];
            foreach ($emotes as $option) {
                if (strpos($comment, $option) !== false) {
                    $isEmote = true;
                }
            }
            if (defaultcolor_isvalid($emote) && $isEmote == true) {
                $args['comment'] = preg_replace(
                    '!^(:{1,2}|\/me|\/npc)!',
                    '${1}' . $emote,
                    $comment 
                );
                break;
            }
            if (defaultcolor_isvalid($color)) {
                if (strpos($comment, '/ooc') === 0) {
                    $args['comment'] = str_replace(
                        '/ooc',
                        '/ooc' . $color,
                        $comment
                    );
                    break;
                }
                $args['comment'] = "$color$comment";
            }
            break;
        case 'insertcomment':
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
                            $script.= ' if('.$emote_js_check.$gm_js_check.'){
                                var nameLen='.$name_len.';
                                oldColor="colLtWhite";'
                                        ."\n".$gm_js_code
                                        .'      newColor="'.$replace_emote.'";
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
                            .'  preview.innerHTML=strStart+strEnd.replace(oldColor,newColor);
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
            break;
    }
    return $args;
}