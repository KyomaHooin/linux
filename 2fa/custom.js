
function clip(service) {
	var range = document.createRange();
	range.selectNode(document.getElementById(service));
	window.getSelection().removeAllRanges();
	window.getSelection().addRange(range);
	document.execCommand("copy");
	window.getSelection().removeAllRanges();
}

async function token(format) {
	controller = new AbortController();
	return await fetch('/2fa/', {
		method: 'POST',
		headers: {
			'Content-Type': 'text/plain',
		},
		body: format
	})
	.then(response => {
		if (!response.ok) {
			throw new Error('Network error.');
	}
		 return response.text();
	})
	.catch(error => {
		console.error(error);
		return false;
	});
}

async function gen_token(format) {

	const tk = await this.token(format);

	if (tk) {
		document.getElementById(format).innerHTML = tk;
		
	} else {
		document.getElementById(format).innerHTML = "error";
	}
}
