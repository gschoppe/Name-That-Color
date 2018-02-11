<?php

$install_url = "https://slack.com/oauth/authorize";
$client_id   = "10356866166.310155287042";
$scopes      = array(
	"command"
);

$id    = urlencode( $client_id );
$scope = urlencode( implode( ',', $scopes ) );
$url   = $install_url . '?client_id=' . $id . "&scope=" . $scope;

header( 'Location: ' . $url );
