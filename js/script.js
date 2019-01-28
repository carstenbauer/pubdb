
function extractNumbers(str){
	var numberPattern = /\d+/g;
	return str.match(numberPattern);
}


function escapeRegExp(str) {
    return str.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
}
function replaceAll(str, find, replace) {
  return str.replace(new RegExp(escapeRegExp(find), 'g'), replace);
}
function LaTeX2MathJax(str){
	return replaceAll(str, "$","%!");
}

function apsIdToString(id){
	var numbers = extractNumbers(id);
	var s = "<b>"+numbers[0].toString().concat("</b>, ").concat(numbers[1].toString());
	if (id.includes("(R)"))
		s = s.concat("(R)");
	return s;
}
function arxivIdToString(id){
	var numbers = extractNumbers(id);
	return numbers[0].toString().concat(".").concat(numbers[1].toString());
}
function natureIdToString(id){
	var numbers = extractNumbers(id);
	return numbers[0].toString();
}

function journalToString(pub){
	var journal = pub.journal.toString();
	var id = pub.identifier.toString();
	if (pub.hasOwnProperty('volume') && pub.volume != null && pub.volume.toString()!=0) {
		var volume = pub.volume.toString();	
	} else 
		var volume = "";
	if (pub.hasOwnProperty('number') && pub.number != null && pub.number.toString()!=0) {
		var number = pub.number.toString();
	} else 
		var number = "";

	switch(journal){
	    case "pra": return "Phys. Rev. A ".concat(apsIdToString(id));
	    case "prb": return "Phys. Rev. B ".concat(apsIdToString(id));
	    case "prc": return "Phys. Rev. C ".concat(apsIdToString(id));
	    case "prd": return "Phys. Rev. D ".concat(apsIdToString(id));
	    case "pre": return "Phys. Rev. E ".concat(apsIdToString(id));
        case "prx": return "Phys. Rev. X ".concat(apsIdToString(id));
	    case "prmaterials": return "Phys. Rev. Materials ".concat(apsIdToString(id));
	    case "prl": return "Phys. Rev. Lett. ".concat(apsIdToString(id));
	    case "rmp": return "Rev. Mod. Phys. ".concat(apsIdToString(id));
	    case "arxiv": return "arXiv:".concat(arxivIdToString(id));
	    case "ncomms": 
	    	if (volume!="" && number !="")
	    		return "Nature Comm. ".concat("<b>"+volume+"</b>").concat(", ").concat(number);
	    	else
	    		return "Nature Comm. ".concat(natureIdToString(id));
	    case "nphys":
	    	if (volume!="" && number !="")
	    		return "Nature Phys. ".concat("<b>"+volume+"</b>").concat(", ").concat(number);
	    	else
	    		return "Nature Phys. ".concat(natureIdToString(id));
	    case "srep":
	    	if (volume!="" && number !="")
	    		return "Scientific Reports ".concat("<b>"+volume+"</b>").concat(", ").concat(number);
	    	else
	    		return "Scientific Reports ".concat(natureIdToString(id));
	    case "nature":
	    	if (volume!="" && number !="")
	    		return "Nature ".concat("<b>"+volume+"</b>").concat(", ").concat(number);
	    	else
	    		return "Nature ".concat(natureIdToString(id));
	    default:
	    	if (volume!="" && number !="")
	    		return journal.concat(" <b>"+volume+"</b>").concat(", ").concat(number);
	    	else
	    		return journal.concat(" ").concat(id.toString());
	}
}

function isManualEntry(pub){
	var journals = ["pra", "prb", "prc", "prd", "pre", "prl", "prmaterials", "rmp", "arxiv", "ncomms", "nphys", "srep", "nature"];
	if (journals.indexOf(pub.journal.toString()) > -1)
		return false;
	else
		return true;
}

function PublicationToHTMLString(pub){
	if (Array.isArray(pub.authors)){
		if (pub.authors.length > 2) 
			var authors_str = pub.authors.slice(0, -1).join(', ')+', and '+pub.authors.slice(-1);
		else if (pub.authors.length == 2)
			var authors_str = pub.authors[0].toString()+" and "+pub.authors[1].toString();
		else
			var authors_str = pub.authors[0].toString();
	}
	else
		var authors_str = pub.authors.toString();

    var project_str = "";
    if ("projects" in pub)
    	if (pub.projects[0].toString() != "Z")
    		var project_str = "(".concat(pub.projects.join(", ")).concat(")");

    var paper_str = project_str.concat(" ").concat(authors_str).concat(", <a href=").concat(pub.url.toString()).concat(" target=_blank>").concat(LaTeX2MathJax(pub.title.toString())).concat("</a>, ").concat(journalToString(pub)).concat(" (").concat(pub.year.toString()).concat(")");
    var id = pub.identifier.toString();
    if (id.includes("(E)"))
		paper_str = paper_str.concat(", Editors' Suggestion");
    return paper_str;
}


function UnCryptMailto( s )
{
    var n = 0;
    var r = "";
    for( var i = 0; i < s.length; i++)
    {
        n = s.charCodeAt( i );
        if( n >= 8364 )
        {
            n = 128;
        }
        r += String.fromCharCode( n - 1 );
    }
    return r;
}

function linkTo_UnCryptMailto( s )
{
    location.href=UnCryptMailto( s );
}
