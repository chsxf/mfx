if (!String.prototype.format) {
	String.prototype.format = function() {
		var args = arguments;
		return this.replace(/{(\d+)}/g, function(match, number) { 
			return (typeof args[number] != 'undefined') ? args[number] : match;
		});
	};
}

if (!String.prototype.trim) {
	String.prototype.trim = function () {
		return this.replace(/^\s+|\s+$/gm, '');
	};
}

if (!String.prototype.pad) {
	String.prototype.pad = function(newLength, char, padOnRight) {
		var originalLength = this.length;
		if (originalLength >= newLength)
			return this;
		
		var str = '';
		for (var i = 0; i < newLength - originalLength; i++)
			str += char;
		str = (padOnRight) ? (this + str) : (str + this);
		return str;
	}
}