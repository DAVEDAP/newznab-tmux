<?phpdefine('FS_ROOT', realpath(dirname(__FILE__)));use newznab\db\DB;$r = new Releases();$rv = new ReleaseExtra();$db = new DB();$rels = $db->query("select releaseID, videowidth, videoheight from releasevideo where definition is null");foreach($rels as $rel){	$sql = sprintf("update releasevideo set definition = %d where releaseID = %d",			$rv->determineVideoResolution($rel["videowidth"], $rel["videoheight"]), $rel["releaseID"]);	$db->exec($sql);}?>