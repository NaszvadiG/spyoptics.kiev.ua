<?php

/** ImageDbProcessor model
 *
 *	Provides methods to resize/crop images; methods to add sugnlasses images to DB from folder.
 *  Needs some refactoring, but works...
 *
 *****************************************************
 * Used libraries
 *****************************************************
 *	php GD graphics library;
 *	resize_image function, copied from stackoverflow (located at './resources/resize_image.php').
 	link: http://stackoverflow.com/questions/14649645/resize-image-in-php).
 *****************************************************
 *
 */
class ImageDbProcessor extends CI_Model {
	function __construct() {
		parent::__construct();
	}

	/** resize method
	 *
	 *	Resizes all images from $folder and saves them to './pub/' ('./pub/' must have access 666 or higher!).
	 *
	 *	Uses resize_image() function copied from stackoverflow (located at './resources/resize_image.php').
	 *
	 *	@param $folder folder where images to be resized are located.
	 *	@param $height output height of resized images.
	 */
	public function resize($folder, $height, $outputDir = '') {
		// config
		//$folder = 'assets/img/'.$folder.'/'; // folder path WITH trailing slash!
        $folder = $folder . '/';

		$outputHeight = $height;
        if ($outputDir) {
            $outputDir = 'pub/' . $outputDir;
            if (!file_exists($outputDir)) {
                mkdir($outputDir);
            }
        } else {
            $outputDir = 'pub';
        }

		// include resize_image function, which I copied from stackoverflow
		require_once 'resources/resize_image.php';

		$imagePaths = $this->getImagePaths($folder);

		// resize and save images
		foreach($imagePaths as $imagePath) {
			list($width, $height) = getimagesize($imagePath);
			$image = resize_image($imagePath, $width, $outputHeight); // old image width is passed to resize function, but it preserves ratio anyway
			// save image
			$newImagePath = $outputDir . '/'.end(explode('/', $imagePath));
			imagejpeg($image, $newImagePath);
		}
	}

    /**
     * Add new model images from folder, resizing them to 3 sizes, and adding all the paths to db
     */

    public function addModelImagesFromFolder($imgSrcDir, $model) {
        $tempDir = 'pub/' . $imgSrcDir; // '.' is referred to application root
        $outputDir = 'assets/img/' . $imgSrcDir;
        $imgSrcPath = 'assets/img/' . $imgSrcDir;
        $outputHeight = 700;
        $miniHeight = 300;
        $thumbnailHeight = 60;

        $this->resize($imgSrcPath, $outputHeight, $imgSrcDir); 
        $this->resize($tempDir, $miniHeight, $imgSrcDir . '/mini');
        $this->resize($tempDir, $thumbnailHeight, $imgSrcDir . '/thumbnail');

        rename($outputDir, $outputDir . '-source');
        rename($tempDir, $outputDir);

        //$this->addToDbByImages($imgSrcDir, $model);
    }

	/** cropAndResize method
	 *	
	 *	Crops and resizes images from $folder.
	 *	Saves modified images to './pub' directory ('./pub/' must have access 666 or higher!).
	 *
	 *	Crop rectangle is specified in the beginning of this method.
	 *	
	 *	Uses resize_image() function copied from stackoverflow (located at './resources/resize_image.php').
	 *
	 *	@param $folder folder with images to be cropped and resized.
	 *	@param $height output height of resized images.
	 *
	 */
	public function cropAndResize($folder, $height) {
		//config
		$cropRect = array(
			'x' => 0,
			'y' => 0,
			'width' => 737,
			'height' => 300
		);
		$outputHeight = $height;

		$folder = 'assets/img/'.$folder.'/';

		// include resize_image function, which I copied from stackoverflow
		include 'resources/resize_image.php';

		$imagePaths = $this->getImagePaths($folder);

		foreach($imagePaths as $imagePath) {
			$image = imagecreatefromjpeg($imagePath);
			list($width, $height) = getimagesize($imagePath);

			// crop image		
			$image = imagecrop($image, $cropRect);

			$oldImageName = end(explode('/', $imagePath));
			$newImagePath = './pub/'.$oldImageName;
			// save cropped image
			imagejpeg($image, $newImagePath); 
			// now resize saved image and resave it (don't know a work around to save the image only once :) )
			$image = resize_image($newImagePath, $width, $outputHeight); // old image's width is passed, but the function saves ratio anyway
			imagejpeg($image, $newImagePath);
		}
	}

	/** addToDbByImages method 
	 *
	 *	Adds sunglasses to database by images located in folder, specified in the config section of this method.
	 *	Reads folder named '$folder', generates pathes to all the images in this folder, and creates new sunglasses in database with these image pathes.
	 *	
	 *
	 */
	public function addToDbByImages($folder, $model) {
		// config
        // name of folder with images
		//$folder = 'touring';
        $folder = $folder . '/';
		//$model = 'Touring';
		$price = 350;

		// get image paths
		$this->load->helper('directory');

        $folderPath = FCPATH . 'assets/img/'.$folder;
        $imageNames = directory_map($folderPath);
        if (empty($imageNames)) {
            echo "Error: can't find images in $folderPath";
            die();
        }

		$i = 0;
		foreach($imageNames as $imageName) {
			if(is_string($imageName)) {
				$images[$i]['path'] = $folder.$imageName;
				$images[$i]['mini_path'] = $folder."mini/".$imageName;
				$images[$i]['thumbnail_path'] = $folder."thumbnail/".$imageName;
				$images[$i]['color'] = substr($imageName, 8, -4);
				$i++;
			}
		}

		foreach($images as $image) {
			$sql = "INSERT INTO sunglasses (
				model, img_path, mini_img_path, thumbnail_img_path, color, price
			) 
			VALUES (
				'".$model."', '".$image['path']."', '".$image['mini_path']."', '".$image['thumbnail_path']."', '".$image['color']."', ".$price." 
			)";
			echo $sql."<br />";
			$this->db->query($sql);
		}
	}

	/** addMiniatures method 
	 *	Updates DB with pathes of miniature images (sets 'mini_img_path'). Miniature names must be the same as original image names.
	 *
	 *	@param $model name of folder (model), where original full-size images located. Exapmles: kenBlockHelm, flynn.
	 *	@param $subfolder name of subfolder, where miniature images located.
	 */
	public function addMiniatures($model) {
		// config
		$folder = 'images/'.$model.'/';
		$folderMini = $folder. 'mini' .'/';

		// get image paths
		$this->load->helper('directory');

		$imageNames = directory_map('./'.$folderMini);

		$i=0;
		foreach($imageNames as $imageName) {
			$imagePaths[$i]['mini'] = $folderMini.$imageName;
			$imagePaths[$i]['original'] = $folder.$imageName;
			$i++;
		}

		foreach($imagePaths as $imagePath) {
			$sql = "UPDATE sunglasses SET mini_img_path = '".$imagePath['mini']."' WHERE img_path='".$imagePath['original']."'";		
			echo $sql."<br />";
			$this->db->query($sql);
		}
	}

	/** addThumbnails method 
	 *	Updates DB with pathes of thumbnails images (sets 'thumbnail_img_path'). Miniature names must be the same as original image names.
	 *
	 *	@param $model name of folder (model), where original full-size images located. Exapmles: kenBlockHelm, flynn.
	 *	@param $subfolder name of subfolder, where thumbnail images located.
	 */
	public function addThumbnails($model) {
		// config
		$folder = 'images/'.$model.'/';
		$folderMini = $folder. 'thumbnail' .'/';

		// get image paths
		$this->load->helper('directory');

		$imageNames = directory_map('./'.$folderMini);

		$i=0;
		foreach($imageNames as $imageName) {
			$imagePaths[$i]['thumbnail'] = $folderMini.$imageName;
			$imagePaths[$i]['original'] = $folder.$imageName;
			$i++;
		}

		foreach($imagePaths as $imagePath) {
			$sql = "UPDATE sunglasses SET thumbnail_img_path = '".$imagePath['thumbnail']."' WHERE img_path='".$imagePath['original']."'";		
			echo $sql."<br />";
			$this->db->query($sql);
		}
	}

	/** relocateImages
	 *	DEPRECATED. Was used only once to remove "images/" prefix in image pathes from database.
	 *
	 */
	public function relocateImages($oldFolder="images", $newFolder="") {
		$query = $this->db->query("SELECT id, img_path, mini_img_path, thumbnail_img_path FROM sunglasses");
		foreach($query->result_array() as $row) {
			$pattern = "/".$oldFolder."\//";
			$newImagePath = preg_replace($pattern, $newFolder, $row['img_path']);
			$newImagePathMini = preg_replace($pattern, $newFolder, $row['mini_img_path']);
			$newImagePathThumbnail = preg_replace($pattern, $newFolder, $row['thumbnail_img_path']);
			echo $newImagePath."<br />";
			echo $newImagePathMini."<br />";
			echo $newImagePathThumbnail."<br />";
			$id = $row['id'];
			$this->db->query("UPDATE sunglasses SET img_path = '".$newImagePath."' WHERE id=".$id);
			$this->db->query("UPDATE sunglasses SET mini_img_path = '".$newImagePathMini."' WHERE id=".$id);
			$this->db->query("UPDATE sunglasses SET thumbnail_img_path = '".$newImagePathThumbnail."' WHERE id=".$id);
		}
	}

	/** getImagePaths method
	 *
	 *	Returns array of image paths of all images from folder $folder
	 *
	 *  @param $folder	folder with images
	 */
	private function getImagePaths($folder) {
		$this->load->helper('directory');

		$imageNames = directory_map('./'.$folder);
		foreach($imageNames as $imageName) {
			if(is_string($imageName)) {
				$path = base_url().$folder.$imageName;
				$imagePaths[] = $path;
			}
		}

		return $imagePaths;
	}

}
