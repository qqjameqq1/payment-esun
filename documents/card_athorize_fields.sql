ALTER TABLE  `cht_order` ADD  `RC` VARCHAR( 255 ) NOT NULL COMMENT  '回覆碼',
ADD  `LTD` VARCHAR( 255 ) NOT NULL COMMENT  '收單交易日期',
ADD  `LTT` VARCHAR( 255 ) NOT NULL COMMENT  '收單交易時間',
ADD  `RRN` VARCHAR( 255 ) NOT NULL COMMENT  '簽帳單序號',
ADD  `AIR` VARCHAR( 255 ) NOT NULL COMMENT  '授權碼';