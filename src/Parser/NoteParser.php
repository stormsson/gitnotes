<?php
namespace Notes\Parser;

class NoteParser {
    const NOTE_TAGS="@noteTags";
    const NOTE_TITLE="@noteTitle";

    public function parseTags($text)
    {
        $matches=[];
        $result = false;
        $noteTagsExpr = "/".self::NOTE_TAGS." (.*?)\n/i";
        if(preg_match($noteTagsExpr, $text, $matches)) {
            $result=[];
            $matches = explode(",", $matches[1]);

            foreach ($matches as $tag) {
                $result[] = trim($tag);
            }
        }

        return $result;
    }

    public function parseTitle($text) {
        $matches=[];
        $result = false;
        $noteTagsExpr = "/".self::NOTE_TITLE." (.*?)\n/i";
        if(preg_match($noteTagsExpr, $text, $matches)) {
            $result = $matches[1];
        }

        return $result;
    }
}
