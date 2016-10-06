<?php

include_once('config/dbconfig.php');

class db {

public $conn = false;

function connect() {
    $this->conn = new mysqli(SERVERNAME, USERNAME, PASSWORD, DBNAME);
    return !$this->conn->connect_error;
}

function close() {
    $this->conn->close();
}

function getPublications() {
    $sql = "SELECT * FROM Publications";
    $result = $this->conn->query($sql);
    if ($result->num_rows <= 0) {
        return false;
    }
    $publications = array();
    while($row = $result->fetch_assoc()){
        $pub = $row;
        $pub["authors"] = explode(", ",$row["authors"]);
        array_push($publications,$pub);
    }

    # Till now, every publication is missing the "projects" key
    $pubs = array();
    foreach($publications as $pub){
        array_push($pubs,$this->addProjectsToPublication($pub));   
    }


    return $pubs; 
}

private function addProjectsToPublication($pub){
    $projects = $this->getProjectsOfPublication($pub);
    $newpub = $pub;
    if ($projects !== false) {
        $newpub["projects"] = $projects;
    }
    return $newpub;
}

function getProjects() {
    $sql = "SELECT * FROM Projects";
    $result = $this->conn->query($sql);
    $projects = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()){
            array_push($projects,$row);
        }
    }
    return $projects;
}

function getIDofProject($project) {
    // Takes project abbrev string
    $sql = "SELECT id, abbrev FROM Projects WHERE abbrev='".$project."'";
    $result = $this->conn->query($sql);
    if ($result->num_rows > 0) {
        // output data of each row
        $row = $result->fetch_assoc();
        return $row["id"];
    } else {
        return false;
    }
}

function getPubIDsOfProject($project) {
    // Takes project abbrev string
    
    $projectid = $this->getIDofProject($project);
    $pubids = array();
    if ($projectid){
    	$sql = "SELECT id, pubid, projectid FROM LinkPublicationsProjects WHERE projectid='".$projectid."'";
	$result = $this->conn->query($sql);
	if ($result->num_rows > 0) {
	    // output data of each row
	    while($row = $result->fetch_assoc()){
	    	array_push($pubids, $row["pubid"]);
	    }
        }
    } else { return false; }

    return $pubids;

}

function getPublicationsOfProject($project) {
    // Takes project abbrev string

    $pubids = $this->getPubIDsOfProject($project);   
    if ($pubids===false) {
        return false;
    }

    if (empty($pubids)) {
        return array();
    }

    # Gather publications
    $publications = array();
    $ids = join("','",$pubids);   
    $sql = "SELECT * FROM Publications WHERE id IN ('$ids')";
    $result = $this->conn->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()){
            $pub = $row;
            $pub["authors"] = explode(", ",$row["authors"]);
            array_push($publications, $pub);
        }
    } else {
        return array();
    }

    # Till now, every publication is missing the "projects" key
    $pubs = array();
    foreach($publications as $pub){
        array_push($pubs,$this->addProjectsToPublication($pub));   
    }

    return $pubs;
}

function getProjectsOfPublication($pub){
    if (!isset($pub["id"])){
        return false;
    }

    # Look up project ids
    $projectids = array();
    $sql = "SELECT projectid FROM LinkPublicationsProjects WHERE pubid='".$pub["id"]."'";
    $result = $this->conn->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()){
            array_push($projectids, $row["projectid"]);
        }
    } else {
        return array();
    }

    # Look up corresponding abbrevs
    $projects = array();
    $ids = join("','",$projectids);   
    $sql = "SELECT abbrev FROM Projects WHERE id IN ('$ids')";
    $result = $this->conn->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()){
            array_push($projects, $row["abbrev"]);
        }
    } else {
        return array();
    }

    return $projects;
}



function insertPaper($paper){
    # Insert publication into db
    $authors_str = $this->escape(join(", ", $paper["authors"]));
    $sql = "INSERT INTO Publications (authors, title, year, url, journal, identifier, bibtex) VALUES ('".$authors_str."', '".$this->escape($paper["title"])."', '".$paper["year"]."', '".$paper["url"]."', '".$paper["journal"]."', '".$paper["identifier"]."', '".$this->escape($paper["bibtex"])."')";
    $result = $this->conn->query($sql);
    if (!$result) {
        return False;
    }
    
    # Add links to corresponding projects
    $pubid = $this->conn->insert_id; // get auto increment id of last query
    foreach($paper["projects"] as $project){
        $projectid = $this->getIDofProject($project);
        $sql = "INSERT INTO LinkPublicationsProjects (pubid, projectid) VALUES (".$pubid.", ".$projectid.")";
        $this->conn->query($sql);
    }

    return True;
}

function removePaper($paper){
    if (!isset($paper["id"])) return False;

    $sql = "DELETE FROM Publications WHERE id='".$paper["id"]."'";
    $result = $this->conn->query($sql);
    if (!$result) return False;

    # Remove links to corresponding projects
    $sql = "DELETE FROM LinkPublicationsProjects WHERE pubid='".$paper["id"]."'";
    $result = $this->conn->query($sql);
    #if (!$result) return False;

    return True;
}

function escape($str){
    $strr = str_replace("'","''",$str);
    $strr = str_replace("\\","\\\\",$strr);
    return $strr;
}

}

?>
