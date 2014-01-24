<?php

/**
 *
 *
 * @author Mathias Meisfjordskar, University of Oslo.
 *         <mathias.meisfjordskar@usit.uio.no>
 * @package simpleSAMLphp
 * @version $Id$
 */

$authStateId = $_REQUEST['AuthState'];

// sanitize the input
$restartURL = SimpleSAML_Utilities::getURLFromStateID($authStateId);
if (!is_null($restartURL)) {
	SimpleSAML_Utilities::checkURLAllowed($restartURL);
}

$state = SimpleSAML_Auth_State::loadState($authStateId, sspmod_negotiate_Auth_Source_Negotiate::STAGEID);

$metadata = SimpleSAML_Metadata_MetaDataStorageHandler::getMetadataHandler();
$idpid = $metadata->getMetaDataCurrentEntityID('saml20-idp-hosted', 'metaindex');
$idpmeta = $metadata->getMetaData($idpid, 'saml20-idp-hosted');

if (isset($idpmeta['auth'])) {
	$source = SimpleSAML_Auth_Source::getById($idpmeta['auth']);
	if ($source === NULL)
		throw new SimpleSAML_Error_BadRequest('Invalid AuthId "' . $idpmeta['auth'] . '" - not found.');

	$session = SimpleSAML_Session::getInstance();
	$session->setData('negotiate:disable', 'session', FALSE, 24*60*60);
	SimpleSAML_Logger::debug('Negotiate(retry) - session enabled, retrying.');
	$source->authenticate($state);
	assert('FALSE');
} else {
	SimpleSAML_Logger::error('Negotiate - retry - no "auth" parameter found in IdP metadata.');
	assert('FALSE');
}
