module.exports = function(grunt) {
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		cssmin: {
		  	target: {
			    files: [{
			      	expand: true,
			      	cwd: 'public/css',
			      	src: ['app.css'],
			      	dest: 'public/css',
			      	ext: '.min.css'
			    }]
		  	}
		},
		uglify: {
		    my_target: {
		      	files: {
		        	'public/js/app.min.js': ['resources/assets/js/app.js'],

		        	// administration
		        	'public/js/admin/user.min.js': ['resources/assets/js/admin/user.js'],
		        	'public/js/admin/role.min.js': ['resources/assets/js/admin/role.js'],
		        	'public/js/admin/system_code.min.js': ['resources/assets/js/admin/system_code.js'],

		        	// master
		        	'public/js/master/bank.min.js': ['resources/assets/js/master/bank.js'],
		        	'public/js/master/account.min.js': ['resources/assets/js/master/account.js'],
		        	'public/js/master/account_classification.min.js': ['resources/assets/js/master/account_classification.js'],
		        	'public/js/master/currency.min.js': ['resources/assets/js/master/currency.js'],
		        	'public/js/master/supplier.min.js': ['resources/assets/js/master/supplier.js'],
		        	'public/js/master/supplier_transaction.min.js': ['resources/assets/js/master/supplier_transaction.js'],
		        	'public/js/master/supplier_type.min.js': ['resources/assets/js/master/supplier_type.js'],
		        	'public/js/master/supplier_group.min.js': ['resources/assets/js/master/supplier_group.js'],
		        	'public/js/master/customer.min.js': ['resources/assets/js/master/customer.js'],
		        	'public/js/master/customer_transaction.min.js': ['resources/assets/js/master/customer_transaction.js'],
		        	'public/js/master/customer_type.min.js': ['resources/assets/js/master/customer_type.js'],
		        	'public/js/master/customer_group.min.js': ['resources/assets/js/master/customer_group.js'],
		        	'public/js/master/territory.min.js': ['resources/assets/js/master/territory.js'],
		        	'public/js/master/item.min.js': ['resources/assets/js/master/item.js'],
		        	'public/js/master/item_transaction.min.js': ['resources/assets/js/master/item_transaction.js'],
		        	'public/js/master/item_warehouse.min.js': ['resources/assets/js/master/item_warehouse.js'],
		        	'public/js/master/item_supplier.min.js': ['resources/assets/js/master/item_supplier.js'],
		        	'public/js/master/item_category.min.js': ['resources/assets/js/master/item_category.js'],
		        	'public/js/master/brand.min.js': ['resources/assets/js/master/brand.js'],
		        	'public/js/master/uom.min.js': ['resources/assets/js/master/uom.js'],
		        	'public/js/master/tax.min.js': ['resources/assets/js/master/tax.js'],
		        	'public/js/master/warehouse.min.js': ['resources/assets/js/master/warehouse.js'],

		        	// purchase
		        	'public/js/purchase/order.min.js': ['resources/assets/js/purchase/order.js'],
		        	'public/js/purchase/order_line.min.js': ['resources/assets/js/purchase/order_line.js'],
		        	'public/js/purchase/item_receipt.min.js': ['resources/assets/js/purchase/item_receipt.js'],
		        	'public/js/purchase/invoice.min.js': ['resources/assets/js/purchase/invoice.js'],
		        	
		        	// sales
		        	'public/js/sales/order.min.js': ['resources/assets/js/sales/order.js'],
		        	'public/js/sales/order_line.min.js': ['resources/assets/js/sales/order_line.js'],
		        	'public/js/sales/delivery.min.js': ['resources/assets/js/sales/delivery.js'],
		        	'public/js/sales/invoice.min.js': ['resources/assets/js/sales/invoice.js'],

		        	// finance
		        	'public/js/finance/cash_receipt.min.js': ['resources/assets/js/finance/cash_receipt.js'],
		        	'public/js/finance/cash_receipt_line.min.js': ['resources/assets/js/finance/cash_receipt_line.js'],
		        	'public/js/finance/cash_payment.min.js': ['resources/assets/js/finance/cash_payment.js'],
		        	'public/js/finance/cash_payment_line.min.js': ['resources/assets/js/finance/cash_payment_line.js'],
		        	'public/js/finance/general_journal.min.js': ['resources/assets/js/finance/general_journal.js'],
		        	'public/js/finance/general_journal_line.min.js': ['resources/assets/js/finance/general_journal_line.js'],
		        	'public/js/finance/account_transaction.min.js': ['resources/assets/js/finance/account_transaction.js'],
		        	'public/js/finance/account_history.min.js': ['resources/assets/js/finance/account_history.js'],
		      	}
		    }
		}
	});

	// Load the Grunt plugins.
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.registerTask('build', ['cssmin', 'uglify']);
};
