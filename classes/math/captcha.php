<?php

class Math_Captcha {

	/**
	 *
	 * @var Kohana_Config
	 */
	protected $_config;
	
	/**
	 *
	 * @var Formula
	 */
	protected $_formula;

	/**
	 * Max integral top limit
	 * @var integer 
	 */
	protected $_max_upper;

	/**
	 * Min integral bottom limit
	 * @var integer 
	 */
	protected $_min_lower;

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

	/**
	 * 
	 * @param Config $config
	 */
	public function __construct(Config $config = null) {
		if (!$config) {
			$config = Kohana::$config->load('math_captcha');
		}
		$this->_init();
	}

	/**
	 * Initialization
	 */
	protected function _init() {
		$this->_max_upper = (int) rand(0, $this->_config['max_upper']);
		$this->_min_lower = (int) rand($this->_config['min_lower'], 0);
		$this->_summand_count = (int) $this->_config['summand_count'];
		$this->_symbol_size = (int) $this->_config['symbol_size'];
		$this->_symbol_padding = (int) $this->_config['symbol_padding'];$this->_d = (int) $this->_config['symbol_padding'];
		$this->_formula = new Formula();
		
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
		$width = imagesx($formula->image) + $this->_symbol_padding;
		$height = imagesy($formula->image) + $this->_symbol_padding;
		$image = imagecreatetruecolor($w, $h);
		imagefill($image, 0, 0, 0xFFFFFF);
		imagecopy($image, $expression->image, 10, 10, 0, 0, $width - 20, $height - 20);
		return $image;
	}

	/**
	 * Calculate formula answer and generate formula 
	 */
	protected function _calculate() {
		$this->_formula = "int{" . $this->_min_lower . "}{" . $this->_max_upper . "}{";
		$this->_answer = '';
		for ($i = 0; $i < $this->_summand_count; $i++) {
			$k = rand(1, 5); // Koeficient before x
			$p = rand(1, 5); // exponent x
			$sign = rand(0, 2) == 0 ? 0 : 1; // + sign is 2 type more often 
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
			$this->_answer+=($k * pow($this->_max_upper, $p + 1) / ($p + 1)) - ($k * pow($this->_min_lower, $p + 1) / ($p + 1));
		}
		$this->_formula.="dx}";
	}

}