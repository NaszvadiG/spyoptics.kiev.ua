<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/** ImageDbProcessorController
 *	
 *	Provides interface for using ImageDbProcessor model.
 *	It is currently used only by developer to automate process of adding product images, and resizing them.
 *	But is intented to be used in e-shop's backend when it's developed.
 *
 */
class ImageDbProcessorController extends CI_Controller {

	/** addMiniatures
	 *	Calls addMiniatures() method of ImageDbProcessor model.
	 */
	public function addMiniatures($model, $subfolder) {
		$this->load->model("ImageDbProcessor");
		$this->ImageDbProcessor->addMiniatures($model, $subfolder);
	}

	/** addThumbnails
	 *	Calls addThumbnails() method of ImageDbProcessor model.
	 */
	public function addThumbnails($model, $subfolder) {
		$this->load->model("ImageDbProcessor");
		$this->ImageDbProcessor->addThumbnails($model, $subfolder);
	}

	/** relocateImages
	 *	Calls relocateImages() method of ImageDbProcessor model.
	 */
	public function relocateImages($oldFolder, $newFolder) {
		$this->load->model("ImageDbProcessor");
		$this->ImageDbProcessor->relocateImages($oldFolder, $newFolder="");
	}
	
	/** addToDbByImages
	 *	Calls addToDbByImages() method of ImageDbProcessor model.
	 */
	public function addToDbByImages() {
		$this->load->model("ImageDbProcessor");
		$this->ImageDbProcessor->addToDbByImages();
	}
	
	/** resize 
	 *	Calls resize() method of ImageDbProcessor model.
	 */
	public function resize($folder, $height) {
		$this->load->model("ImageDbProcessor");
		$this->ImageDbProcessor->resize($folder, $height);
	}

	/** cropAndResize 
	 *	Calls cropAndResize() method of ImageDbProcessor model.
	 */
	public function cropAndResize($folder, $height) {
		$this->load->model("ImageDbProcessor");
		$this->ImageDbProcessor->cropAndResize($folder, $height);
	}
}
