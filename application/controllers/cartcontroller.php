<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CartController extends CI_Controller {
	public function add($productId) {
		$this->load->model('Sunglasses');
		$this->Sunglasses->addToCart($productId);
		echo "Adding product to cart";
	}
	public function remove($productId) { 
		$this->load->model('Sunglasses');
		$this->Sunglasses->removeFromCart($productId);

		echo "Removing prodcut from cart";
	}

	/** view method
	 * Loads cart view, showing all the products.
	 */
	public function view() {
		$basket = $this->Basket->getInstance();
		$viewData['cart']['items'] = $basket->getItems();
		$viewData['cart']['totalPrice'] = $basket->getTotalPrice();

		$this->load->view('header/forPageNoCart');	
		$this->load->view('cart/order', $viewData);	
		$this->load->view('footer');	
	}

	public function submitOrder() {
		$basket = $this->Basket->getInstance();
		
		$submitResult = $basket->submitOrder($_POST);

		$this->load->view("header/forPageNoCart");	
		if($submitResult == false) {
			$this->load->view("cart/submitOrderFail");
		} else {
			$basket->removeAll();
			$this->load->view("cart/submitOrderSuccess", $_POST);
		}
		$this->load->view("footer");	
	}
}
