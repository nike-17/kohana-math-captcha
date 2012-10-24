<?php

class Math_Captcha {

	/**
	 *
	 * @var Kohana_Config
	 */
	protected $_config;

	/**
	 * Summand count
	 * @var integer 
	 */
	protected $_summand_count;

	/**
	 * text formula representaion
	 * @var string 
	 */
	protected $_formula;

	/**
	 * Captcha Answer
	 * @var  mix
	 */
	protected $_answer;

	/**
	 * formula symbol size 
	 * @var integer
	 */
	protected $_symbol_size;

	/**
	 * Padding between symbols
	 * @var integer 
	 */
	protected $_symbol_padding;
	protected $_max_upper;
	protected $_min_lower;

	/**
	 * 
	 * @param Config $config
	 */
	public function __construct(Config $config = null) {
		if (!$config) {
			$config = Kohana::$config->load('math_captcha');
		}
		$this->_config = (array) $config;
		$this->_init();
	}

	/**
	 * Initialization
	 */
	protected function _init() {

		$this->_summand_count = (int) $this->_config['summand_count'];
		$this->_symbol_size = (int) $this->_config['symbol_size'];
		$this->_symbol_padding = (int) $this->_config['symbol_padding'];
		$this->_minus_signe_chance = (int) $this->_config['minus_signe_chance'];
	}

	/**
	 * 
	 * @param Array $config
	 */
	public function set_config($config) {
		$this->_config = $config;
	}

	/**
	 * Render formule image
	 * @return 
	 */
	public function render() {

		$expression = new expression_math(tableau_expression(trim($this->_formula)));
		$expression->dessine($this->_symbol_size);
		$width = imagesx($expression->image) + $this->_symbol_padding;
		$height = imagesy($expression->image) + $this->_symbol_padding;
		$image = imagecreatetruecolor($width, $height);
		imagefill($image, 0, 0, 0xFFFFFF);
		imagecopy($image, $expression->image, 10, 10, 0, 0, $width - 20, $height - 20);
		ob_start(); // buffers future output
		imagepng($image); // writes to output/buffer
		$b64 = base64_encode(ob_get_contents()); // returns output
		ob_end_clean(); // clears buffered output
		return "data:image/png;base64," . $b64;
	}

	/**
	 * Calculate formula answer and generate formula 
	 */
	public function calculate() {
		$this->_formula = "int{" . $this->_get_min_lower() . "}{" . $this->_get_max_upper() . "}{";
		$this->_answer = 0;
		for ($i = 0; $i < $this->_summand_count; $i++) {
			$k = rand(1, 5); // Koeficient before x
			$p = rand(1, 5); // exponent x
			$sign = rand(0, $this->_minus_signe_chance) == 0 ? 0 : 1;
			$tt = $k > 1 ? $k : "";
			$tt.="x";
			if ($p > 1) {
				$tt.="^" . $p;
			}
			if ($i == 0) {
				if ($sign == 1) {
					$this->_formula.="({-}" . $tt . ")";
				} else {
					$this->_formula.=$tt;
				}
			} else {
				$this->_formula.=($sign == 1 ? "-" : "+") . $tt;
			}
			if ($sign == 1) {
				$k = -$k;
			}
			$this->_answer+=($k * pow($this->_get_max_upper(), $p + 1) / ($p + 1)) - ($k * pow($this->_get_min_lower(), $p + 1) / ($p + 1));

		}
		$this->_formula.="dx}";
	}

	protected function _get_max_upper() {
		if (!$this->_max_upper) {
			while( $this->_get_min_lower() >= $this->_max_upper ){
				$this->_max_upper = (int) rand(0, $this->_config['max_upper']);
			}
		}
		return $this->_max_upper;
	}

	protected function _get_min_lower() {
		if (!$this->_min_lower) {
			$this->_min_lower = rand($this->_config['min_lower'], 0);
		}
		return $this->_min_lower;
	}

	public function get_answer() {
		return $this->_answer;
	}

	public function get_formula() {
		return $this->_formula;
	}

	public function set_formula($formula) {
		$this->_formula = $formula;
	}

}