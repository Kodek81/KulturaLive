var userFormView = function(template) {

    this.initialize = function() {
        this.el = $('<div/>');
        
        this.el.on('click', '.back-button', function() {
			history.go(-1);
		});
				
    };

    this.render = function() {
        this.el.html(template);
        return this;
    };

    this.initialize();

};