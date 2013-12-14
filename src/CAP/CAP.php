<?php
## Copyright 2013, Blake Crosby
## me@blakecrosby.com

namespace CAP;

class CAP {

    # We need to run different parsing algorithims based on profile.

    public function get($url,$profile = null) {
        if ($profile == 'CA') {
            return $this->getCA($url);
        }

        else {
            return $this->getPlain($url);
        }
    }

    # This function fetches the CAP file from a URL.

    private function fetch($url) {
        #fetch the file
        return file_get_contents($url);
    }

    # Process the CAP using version 1.2 of the specification (no profile attached)
    private function getPlain($url) {

        $xml = simplexml_load_string($this->fetch($url));

        # We don't need everything in the XML. So return
        # specific elements.

        $data->ident = "$xml->identifier";
        $data->sender = "$xml->sender";
        $data->senddate = "$xml->sent";
        $data->status = "$xml->status";
        $data->type = "$xml->msgType";
        $data->source = "$xml->source";
        $data->references = preg_split("/ /",$xml->references);
        foreach ($xml->info as $info){
            $data->body->category = (string)$info->category;
            $data->body->event = (string)$info->event;
            $data->body->urgency = (string)$info->urgency;
            $data->body->severity = (string)$info->severity;
            $data->body->certainty = (string)$info->certainty;
            $data->body->effectivedate = (string)$info->effective;
            $data->body->expirydate = (string)$info->expires;
            $data->body->summary = (string)$info->headline;
            $data->body->details = (string)$info->description;
            $data->body->instructions = (string)$info->instructions;
            $data->body->url = (string)$info->web;

            # We only want certain objects from the area data
            # Iterate through all of the area elements and only set specific elements
            for ($i = 0; $i < count($info->area); $i++) {

                # Set some variables
                $flipped = "";

                $data->body->area[$i]->description = (string)$info->area[$i]->areaDesc;
                $data->body->area[$i]->polygon = (string)$info->area[$i]->polygon;

                # Enhanced Well Known Text expects coordinates to be in X,Y (long,lat) format.
                # We need to iterate through every point and flip the values.
                foreach (preg_split("/ /",(string)$info->area[$i]->polygon) as $coords) {
                    $coordinates = preg_split("/,/",$coords);
                    #array_push($flipped,"$coordinates[1],$coordinates[0]");
                    $flipped = $flipped ." $coordinates[1],$coordinates[0]";
                }
                $data->body->area[$i]->polygonEWKT = "SRID=4326;POLYGON(" . $flipped . ")";
            }
        }

        return($data);

    }

    # Process the CAP using the Canadian Profile
    private function getCA($url) {

        $xml = simplexml_load_string($this->fetch($url));

        # We don't need everything in the XML. So return
        # specific elements.

        $data->ident = "$xml->identifier";
        $data->sender = "$xml->sender";
        $data->senddate = "$xml->sent";
        $data->status = "$xml->status";
        $data->type = "$xml->msgType";
        $data->source = "$xml->source";
        $data->references = preg_split("/ /",$xml->references);
        foreach ($xml->info as $info){
            $data->body->{$info->language}->category = (string)$info->category;
            $data->body->{$info->language}->event = (string)$info->event;
            $data->body->{$info->language}->urgency = (string)$info->urgency;
            $data->body->{$info->language}->severity = (string)$info->severity;
            $data->body->{$info->language}->certainty = (string)$info->certainty;
            $data->body->{$info->language}->effectivedate = (string)$info->effective;
            $data->body->{$info->language}->expirydate = (string)$info->expires;
            $data->body->{$info->language}->summary = (string)$info->headline;
            $data->body->{$info->language}->details = (string)$info->description;
            $data->body->{$info->language}->instructions = (string)$info->instructions;
            $data->body->{$info->language}->url = (string)$info->web;

            # We only want certain objects from the area data
            # Iterate through all of the area elements and only set specific elements
            for ($i = 0; $i < count($info->area); $i++) {

                # Set some variables
                $flipped = "";

                $data->body->{$info->language}->area[$i]->description = (string)$info->area[$i]->areaDesc;
                $data->body->{$info->language}->area[$i]->polygon = (string)$info->area[$i]->polygon;

                # Enhanced Well Known Text expects coordinates to be in X,Y (long,lat) format.
                # We need to iterate through every point and flip the values.
                foreach (preg_split("/ /",(string)$info->area[$i]->polygon) as $coords) {
                    $coordinates = preg_split("/,/",$coords);
                    #array_push($flipped,"$coordinates[1],$coordinates[0]");
                    $flipped = $flipped ." $coordinates[1] $coordinates[0],";
                }
                $flipped = rtrim($flipped, ",");
                $data->body->{$info->language}->area[$i]->polygonEWKT = "SRID=4326;POLYGON(" . $flipped . ")";
            }
        }

        return($data);

    }
}

?>