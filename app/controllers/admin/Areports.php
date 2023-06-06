<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Areports extends MY_Controller {
	public $row = 3;

	public function __construct()
    {
        parent::__construct();
        //$this->mBodyClass .= ' sidebar-collapse';
    }   

	public function index() {
		redirect($_SERVER['HTTP_REFERER']);
	}

/**
* 	Balancesheet report function
* 	
*  	@param	string	$download	Optional parameter to download report i.e. download or NULL
*   @param	string	$format		Optional parameter to select download format i.e pdf or xls
* 	@return void
**/
	public function balancesheet($download = NULL, $format = NULL, $startdate = NULL, $enddate = NULL) {
		// set page title
		$this->mPageTitle = lang('page_title_reports_balancesheet');
		$this->data['title'] = lang('page_title_reports_balancesheet');

		$only_opening = false;
		// $startdate = null;
		// $enddate = null;

		if ($download === 'download') {
			if ($startdate && $enddate) {
				$startdate = $this->functionscore->dateToSql($startdate);
				$this->data['startdate'] = $startdate;
				$enddate = $this->functionscore->dateToSql($enddate);
				$this->data['enddate'] = $enddate;
			} else if ($startdate) {
				$startdate = $this->functionscore->dateToSql($startdate);
				$this->data['startdate'] = $startdate;
			} else if ($enddate) {
				$enddate = $this->functionscore->dateToSql($enddate);
				$this->data['enddate'] = $enddate;
			}	
		} else {
			$this->data['startdate'] = NULL;
			$this->data['enddate'] = NULL;
		}

		if ($this->input->method() == 'post') {
			$this->data['options'] = true;
			
			if (!empty($this->input->post('opening'))) {
				$only_opening = true;
				$this->data['only_opening'] = $only_opening;
				/* Sub-title*/
				$this->data['subtitle'] = sprintf(lang('opening_balance_sheet_as_on'), $this->functionscore->dateFromSql($this->mAccountSettings->fy_start));

			} else {
				if ($this->input->post('startdate')) {
					$startdate = $this->functionscore->dateToSql($this->input->post('startdate'));
					$this->data['start_date'] = $startdate;
					$this->data['startdate'] = $this->input->post('startdate');
				}

				if ($this->input->post('enddate')) {
					$enddate = $this->functionscore->dateToSql($this->input->post('enddate'));
					$this->data['end_date'] = $enddate;
					$this->data['enddate'] = $this->input->post('enddate');
				}

				if ( $this->input->post('startdate') && $this->input->post('enddate')) {
					$this->data['subtitle'] = sprintf(lang('balance_sheet_from_to'), $this->functionscore->dateFromSql($this->functionscore->dateToSql($this->input->post('startdate'))), $this->functionscore->dateFromSql($this->functionscore->dateToSql($this->input->post('enddate'))));

				} else if ( $this->input->post('startdate')) {
					$this->data['subtitle'] = sprintf(lang('balance_sheet_from'), $this->functionscore->dateFromSql($this->functionscore->dateToSql($this->input->post('startdate'))));

				} else if ($this->input->post('enddate')) {
					$this->data['subtitle'] = sprintf(lang('balance_sheet_from_to'), $this->functionscore->dateFromSql($this->mAccountSettings->fy_start), $this->functionscore->dateFromSql($this->functionscore->dateToSql($this->input->post('enddate'))));

				} else {
					$this->data['options'] = false;
			
					/* Sub-title*/
					$this->data['subtitle'] = sprintf(lang('closing_balance_sheet_as_on'), $this->functionscore->dateFromSql($this->mAccountSettings->fy_end));
				}
			}
		} else {
			$this->data['options'] = false;
			if ($download === 'download') {
				if ($startdate && $enddate) {
					$this->data['subtitle'] = sprintf(lang('balance_sheet_from_to'), $this->functionscore->dateFromSql($this->functionscore->dateToSql($startdate)), $this->functionscore->dateFromSql($this->functionscore->dateToSql($enddate)));
				} else if ($startdate) {
					$this->data['subtitle'] = sprintf(lang('balance_sheet_from'), $this->functionscore->dateFromSql($this->functionscore->dateToSql($startdate)));
				} else if ($enddate) {
					$this->data['subtitle'] = sprintf(lang('balance_sheet_from_to'), $this->functionscore->dateFromSql($this->mAccountSettings->fy_start), $this->functionscore->dateFromSql($this->functionscore->dateToSql($enddate)));
				} else {
					/* Sub-title*/
					$this->data['subtitle'] = sprintf(lang('closing_balance_sheet_as_on'), $this->functionscore->dateFromSql($this->mAccountSettings->fy_end));
				}
			} else {
				/* Sub-title*/
				$this->data['subtitle'] = sprintf(lang('closing_balance_sheet_as_on'), $this->functionscore->dateFromSql($this->mAccountSettings->fy_end));
			}
		}

		/**********************************************************************/
		/*********************** BALANCESHEET CALCULATIONS ********************/
		/**********************************************************************/
		$this->load->library('AccountList');
		/* Liabilities */
		$liabilities = new AccountList();
		$liabilities->Group = &$this->Group;
		$liabilities->Ledger = &$this->Ledger;
		$liabilities->only_opening = $only_opening;
		$liabilities->start_date = $startdate;
		$liabilities->end_date = $enddate;
		$liabilities->affects_gross = -1;
		$liabilities->start(2);

		$bsheet['liabilities'] = $liabilities;

		$bsheet['liabilities_total'] = 0;
		if ($liabilities->cl_total_dc == 'C') {
			$bsheet['liabilities_total'] = $liabilities->cl_total;
		} else {
			$bsheet['liabilities_total'] = $this->functionscore->calculate($liabilities->cl_total, 0, 'n');
		}

		/* Assets */
		$assets = new AccountList();
		$assets->Group = &$this->Group;
		$assets->Ledger = &$this->Ledger;
		$assets->only_opening = $only_opening;
		$assets->start_date = $startdate;
		$assets->end_date = $enddate;
		$assets->affects_gross = -1;
		$assets->start(1);

		$bsheet['assets'] = $assets;

		$bsheet['assets_total'] = 0;
		if ($assets->cl_total_dc == 'D') {
			$bsheet['assets_total'] = $assets->cl_total;
		} else {
			$bsheet['assets_total'] = $this->functionscore->calculate($assets->cl_total, 0, 'n');
		}

		/* Profit and loss calculations */
		$income = new AccountList();
		$income->Group = &$this->Group;
		$income->Ledger = &$this->Ledger;
		$income->only_opening = $only_opening;
		$income->start_date = $startdate;
		$income->end_date = $enddate;
		$income->affects_gross = -1;
		$income->start(3);

		$expense = new AccountList();
		$expense->Group = &$this->Group;
		$expense->Ledger = &$this->Ledger;
		$expense->only_opening = $only_opening;
		$expense->start_date = $startdate;
		$expense->end_date = $enddate;
		$expense->affects_gross = -1;
		$expense->start(4);

		if ($income->cl_total_dc == 'C') {
			$income_total = $income->cl_total;
		} else {
			$income_total = $this->functionscore->calculate($income->cl_total, 0, 'n');
		}
		if ($expense->cl_total_dc == 'D') {
			$expense_total = $expense->cl_total;
		} else {
			$expense_total = $this->functionscore->calculate($expense->cl_total, 0, 'n');
		}

		$bsheet['pandl'] = $this->functionscore->calculate($income_total, $expense_total, '-');

		/* Difference in opening balance */
		$bsheet['opdiff'] = $this->ledger_model->getOpeningDiff();
		if ($this->functionscore->calculate($bsheet['opdiff']['opdiff_balance'], 0, '==')) {
			$bsheet['is_opdiff'] = false;
		} else {
			$bsheet['is_opdiff'] = true;
		}

		/**** Final balancesheet total ****/
		$bsheet['final_liabilities_total'] = $bsheet['liabilities_total'];
		$bsheet['final_assets_total'] = $bsheet['assets_total'];

		/* If net profit add to liabilities, if net loss add to assets */
		if ($this->functionscore->calculate($bsheet['pandl'], 0, '>=')) {
			$bsheet['final_liabilities_total'] = $this->functionscore->calculate(
				$bsheet['final_liabilities_total'],
				$bsheet['pandl'], '+');
		} else {
			$positive_pandl = $this->functionscore->calculate($bsheet['pandl'], 0, 'n');
			$bsheet['final_assets_total'] = $this->functionscore->calculate(
				$bsheet['final_assets_total'],
				$positive_pandl, '+');
		}

		/**
		 * If difference in opening balance is Dr then subtract from
		 * assets else subtract from liabilities
		 */
		if ($bsheet['is_opdiff']) {
			if ($bsheet['opdiff']['opdiff_balance_dc'] == 'D') {
				$bsheet['final_assets_total'] = $this->functionscore->calculate(
					$bsheet['final_assets_total'],
					$bsheet['opdiff']['opdiff_balance'], '+');
			} else {
				$bsheet['final_liabilities_total'] = $this->functionscore->calculate(
					$bsheet['final_liabilities_total'],
					$bsheet['opdiff']['opdiff_balance'], '+');
			}
		}

		$this->data['bsheet'] = $bsheet;

		if (!$download) {
			// render page
			//$this->render('reports/balancesheet');
			$bc  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('accounts'), 'page' => lang('accounts')], ['link' => '#', 'page' => lang('Accounts')]];
        $meta = ['page_title' => lang('Accounts'), 'bc' => $bc];
        $this->page_construct('accounts/reports_balancesheet', $meta, $this->data);
		}

		if ($download === 'download') {
			// $filename = 'Balance Sheet';
			// $this->load->library('excel');
   //          $this->excel->setActiveSheetIndex(0);
   //          $styleArray = array(
   //              'borders' => array(
   //                  'allborders' => array(
   //                      'style' => PHPExcel_Style_Border::BORDER_THIN
   //                  )
   //              )
   //          );
   //          $this->excel->getDefaultStyle()->applyFromArray($styleArray);
				
			// $this->excel->getActiveSheet()->setTitle('Balance Sheet');
   //          $this->excel->getActiveSheet()->SetCellValue('A1', $this->data['subtitle']);
   //          $this->excel->getActiveSheet()->mergeCells('A1:B1');

   //          $this->excel->getActiveSheet()->SetCellValue('A2', lang('balance_sheet_assets'));
   //          $this->excel->getActiveSheet()->SetCellValue('B2', lang('search_views_legend_amount') . ' (' . $this->mAccountSettings->currency_symbol . ')');
   //          $this->account_st_short($bsheet['assets'], $c = -1, $this, 'D');
   //          $this->excel->getActiveSheet()->SetCellValue('A'.($this->row), lang('balance_sheet_loe'));
   //          $this->excel->getActiveSheet()->SetCellValue('B'.($this->row), lang('search_views_legend_amount') . ' (' . $this->mAccountSettings->currency_symbol . ')');
   //          $this->row++;
   //          $this->account_st_short($bsheet['liabilities'], $c = -1, $this, 'C');

   //          $this->row++;
   //          if ($this->functionscore->calculate($bsheet['assets_total'], 0, '>=')) {
   //          	$this->excel->getActiveSheet()->SetCellValue('A'.($this->row), lang('balance_sheet_total_assets'));
   //          	$this->excel->getActiveSheet()->SetCellValue('B'.($this->row), $this->functionscore->toCurrency('D', $bsheet['assets_total']));
			// } else {
			// 	$this->excel->getActiveSheet()->SetCellValue('A'.($this->row), lang('balance_sheet_total_assets'));
   //          	$this->excel->getActiveSheet()->SetCellValue('B'.($this->row), $this->functionscore->toCurrency('D', $bsheet['assets_total']));
   //          	$header = 'A'.$this->row.':B'.$this->row;
			//     $this->excel->getActiveSheet()->getStyle($header)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ff3300');
			//     $style = array(
			//         'font' => array('bold' => true,),
			//     );
			//     $this->excel->getActiveSheet()->getStyle($header)->applyFromArray($style);
			// }

   //          $this->row++;
			// $header = 'A'.$this->row.':B'.$this->row;
   //          $style = array(
		 //        'font' => array('bold' => true,),
		 //    );
		 //    $this->excel->getActiveSheet()->getStyle($header)->applyFromArray($style);
			// if ($this->functionscore->calculate($bsheet['pandl'], 0, '>=')) {
			// 	$this->excel->getActiveSheet()->SetCellValue('A'.($this->row), ' ');
			// 	$this->excel->getActiveSheet()->SetCellValue('B'.($this->row), ' ');
			// } else {
			// 	$this->excel->getActiveSheet()->SetCellValue('A'.($this->row), lang('balance_sheet_net_loss'));
			// 	$positive_pandl = $this->functionscore->calculate($bsheet['pandl'], 0, 'n');
			// 	$this->excel->getActiveSheet()->SetCellValue('B'.($this->row), $this->functionscore->toCurrency('D', $positive_pandl));
			// }

			// if ($bsheet['is_opdiff']) {
   //          	$this->row++;
			// 	$header = 'A'.$this->row.':B'.$this->row;
			//     $this->excel->getActiveSheet()->getStyle($header)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ff3300');
			//     $style = array(
			//         'font' => array('bold' => true,),
			//     );
			//     $this->excel->getActiveSheet()->getStyle($header)->applyFromArray($style);

			// 	/* If diff in opening balance is Dr */
			// 	if ($bsheet['opdiff']['opdiff_balance_dc'] == 'D') {
			// 		$this->excel->getActiveSheet()->SetCellValue('A'.($this->row), lang('balance_sheet_diff_opp'));
			// 		$this->excel->getActiveSheet()->SetCellValue('A'.($this->row), $this->functionscore->toCurrency('D', $bsheet['opdiff']['opdiff_balance']));
			// 	} else {
			// 		$this->excel->getActiveSheet()->SetCellValue('A'.($this->row), ' ');
			// 		$this->excel->getActiveSheet()->SetCellValue('B'.($this->row), ' ');
			// 	}
			// }

   //          $this->row++;
   //          if ($this->functionscore->calculate($bsheet['final_liabilities_total'], $bsheet['final_assets_total'], '==')) {
   //          	// Add Backgorund Grey
            	
			// 	$header = 'A'.$this->row.':B'.$this->row;
			//     $this->excel->getActiveSheet()->getStyle($header)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('cccdd2');

   //          	$style = array(
			//         'font' => array('bold' => true,),
			//     );
			//     $this->excel->getActiveSheet()->getStyle($header)->applyFromArray($style);
			// } else {
			// 	$header = 'A'.$this->row.':B'.$this->row;
			//     $this->excel->getActiveSheet()->getStyle($header)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ff3300');
			//     $style = array(
			//         'font' => array('bold' => true,),
			//     );
			//     $this->excel->getActiveSheet()->getStyle($header)->applyFromArray($style);
			// }
			// $this->excel->getActiveSheet()->SetCellValue('A'.($this->row), lang('balance_sheet_total'));
			// $this->excel->getActiveSheet()->SetCellValue('B'.($this->row), $this->functionscore->toCurrency('D', $bsheet['final_assets_total']));
   //          $this->row++;


   //          $this->row++;
			// $header = 'A'.$this->row.':B'.$this->row;
   //          $style = array(
		 //        'font' => array('bold' => true,),
		 //    );
		 //    $this->excel->getActiveSheet()->getStyle($header)->applyFromArray($style);
			// if ($this->functionscore->calculate($bsheet['liabilities_total'], 0, '>=')) {
   //          	$this->excel->getActiveSheet()->SetCellValue('A'.($this->row), lang('balance_sheet_tloe'));
   //          	$this->excel->getActiveSheet()->SetCellValue('B'.($this->row), $this->functionscore->toCurrency('C', $bsheet['liabilities_total']));
			// } else {
			// 	$this->excel->getActiveSheet()->SetCellValue('A'.($this->row), lang('balance_sheet_tloe'));
   //          	$this->excel->getActiveSheet()->SetCellValue('B'.($this->row), $this->functionscore->toCurrency('C', $bsheet['liabilities_total']));
   //          	$header = 'A'.$this->row.':B'.$this->row;
			//     $this->excel->getActiveSheet()->getStyle($header)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ff3300');
			//     $style = array(
			//         'font' => array('bold' => true,),
			//     );
			//     $this->excel->getActiveSheet()->getStyle($header)->applyFromArray($style);
			// }
			

   //          $this->row++;
			// $header = 'A'.$this->row.':B'.$this->row;
   //          $style = array(
		 //        'font' => array('bold' => true,),
		 //    );
		 //    $this->excel->getActiveSheet()->getStyle($header)->applyFromArray($style);
			// if ($this->functionscore->calculate($bsheet['pandl'], 0, '>=')) {
			// 	$this->excel->getActiveSheet()->SetCellValue('A'.($this->row), lang('balance_sheet_net_profit'));
			// 	$this->excel->getActiveSheet()->SetCellValue('B'.($this->row), $this->functionscore->toCurrency('C', $bsheet['pandl']));
			// } else {
			// 	$this->excel->getActiveSheet()->SetCellValue('A'.($this->row), ' ');
			// 	$this->excel->getActiveSheet()->SetCellValue('B'.($this->row), ' ');
			// }

			// if ($bsheet['is_opdiff']) {
   //          	$this->row++;
			// 	$header = 'A'.$this->row.':B'.$this->row;
			//     $this->excel->getActiveSheet()->getStyle($header)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ff3300');
			//     $style = array(
			//         'font' => array('bold' => true,),
			//     );
			//     $this->excel->getActiveSheet()->getStyle($header)->applyFromArray($style);

			// 	/* If diff in opening balance is Dr */
			// 	if ($bsheet['opdiff']['opdiff_balance_dc'] == 'C') {
			// 		$this->excel->getActiveSheet()->SetCellValue('A'.($this->row), lang('balance_sheet_diff_opp'));
			// 		$this->excel->getActiveSheet()->SetCellValue('A'.($this->row), $this->functionscore->toCurrency('C', $bsheet['opdiff']['opdiff_balance']));
			// 	} else {
			// 		$this->excel->getActiveSheet()->SetCellValue('A'.($this->row), ' ');
			// 		$this->excel->getActiveSheet()->SetCellValue('B'.($this->row), ' ');
			// 	}
			// }

   //      	$this->row++;
			// if ($this->functionscore->calculate($bsheet['final_liabilities_total'],
			// 	$bsheet['final_assets_total'], '==')) {
			// 	$header = 'A'.$this->row.':B'.$this->row;
			//     $this->excel->getActiveSheet()->getStyle($header)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('cccdd2');
			
			// 	$style = array(
			//         'font' => array('bold' => true,),
			//     );
			//     $this->excel->getActiveSheet()->getStyle($header)->applyFromArray($style);
			// 	// ADD GREY BG
			// } else {
			// 	$header = 'A'.$this->row.':B'.$this->row;
			//     $this->excel->getActiveSheet()->getStyle($header)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ff3300');
			//     $style = array(
			//         'font' => array('bold' => true,),
			//     );
			//     $this->excel->getActiveSheet()->getStyle($header)->applyFromArray($style);
			// }
			// $this->excel->getActiveSheet()->SetCellValue('A'.($this->row), lang('balance_sheet_total'));
			// $this->excel->getActiveSheet()->SetCellValue('B'.($this->row), $this->functionscore->toCurrency('C', $bsheet['final_liabilities_total']));


   //          $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(80);
   //          $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);

			if ($format === 'csv') {
				$name = 'Balancesheet.csv';
	            $html = $this->load->view('reports/downloadcsv/balancesheet', $this->data, TRUE, NULL, NULL, NULL, NULL, 'L');
	            header('Content-Type: application/csv');
            	header('Content-Disposition: attachement; filename="' . $name . '"');
            	echo $html;
			}

			if ($format=='pdf') {
				$name = 'Balancesheet.pdf';

				// $this->load->view('reports/pdf/balancesheet', $this->data);

	            $html = $this->load->view('reports/pdf/balancesheet', $this->data, TRUE, NULL, NULL, NULL, NULL, 'L');
	            $this->functionscore->generate_pdf($html, $name);



                // require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                // $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                // $rendererLibrary = 'MPDF';
                // $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                
                // if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                //     die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                //         PHP_EOL . ' as appropriate for your directory structure');
                // }

                // header('Content-Type: application/pdf');
                // header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                // header('Cache-Control: max-age=0');
                // $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                // $objWriter->save('php://output');
                // exit();
            }

            if ($format=='xls') {
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                header('Cache-Control: max-age=0');
                $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                $objWriter->save('php://output');
                exit();
            }
		}
	}

	function account_st_short($account, $c = 0, $THIS, $dc_type) {
		$counter = $c;
		if ($account->id > 4) {
			if ($dc_type == 'D' && $account->cl_total_dc == 'C' && $this->functionscore->calculate($account->cl_total, 0, '!=')) {
				$header = 'A'.$this->row.':B'.$this->row;
			    $this->excel->getActiveSheet()->getStyle($header)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ff3300');
			    $style = array(
			        'font' => array('bold' => true,),
			    );
			    $this->excel->getActiveSheet()->getStyle($header)->applyFromArray($style);
			} else if ($dc_type == 'C' && $account->cl_total_dc == 'D' && $this->functionscore->calculate($account->cl_total, 0, '!=')) {
				$header = 'A'.$this->row.':B'.$this->row;
			    $this->excel->getActiveSheet()->getStyle($header)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ff3300');
			    $style = array(
			        'font' => array('bold' => true,),
			    );
			    $this->excel->getActiveSheet()->getStyle($header)->applyFromArray($style);
			} else {
				
			}

			// Name of Account
    		$this->excel->getActiveSheet()->SetCellValue('A'.$this->row, $this->print_space($counter) . $this->functionscore->toCodeWithName($account->code, $account->name));

			// Amount in Account
    		$this->excel->getActiveSheet()->SetCellValue('B'.$this->row, $this->functionscore->toCurrency($account->cl_total_dc, $account->cl_total));
    		$this->row++;
		}

		foreach ($account->children_groups as $id => $data) {
			$counter++;
			$this->account_st_short($data, $counter, $THIS, $dc_type);
			$counter--;
		}

		if (count($account->children_ledgers) > 0) {
			$counter++;
			foreach ($account->children_ledgers as $id => $data) {
				
				if ($dc_type == 'D' && $data['cl_total_dc'] == 'C' && $this->functionscore->calculate($data['cl_total'], 0, '!=')) {
					$header = 'A'.$this->row.':B'.$this->row;
				    $this->excel->getActiveSheet()->getStyle($header)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ff3300');
				    $style = array(
				        'font' => array('bold' => true,),
				    );
				    $this->excel->getActiveSheet()->getStyle($header)->applyFromArray($style);
				} else if ($dc_type == 'C' && $data['cl_total_dc'] == 'D' && $this->functionscore->calculate($data['cl_total'], 0, '!=')) {
					$header = 'A'.$this->row.':B'.$this->row;
				    $this->excel->getActiveSheet()->getStyle($header)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ff3300');
				    $style = array(
				        'font' => array('bold' => true,),
				    );
				    $this->excel->getActiveSheet()->getStyle($header)->applyFromArray($style);
				} else {
					
				}

				// Name of Account
	    		$this->excel->getActiveSheet()->SetCellValue('A'.$this->row, $this->print_space($counter) . $this->functionscore->toCodeWithName($data['code'], $data['name']));

				// Amount in Account
	    		$this->excel->getActiveSheet()->SetCellValue('B'.$this->row, $this->functionscore->toCurrency($data['cl_total_dc'], $data['cl_total']));
				$this->row++;
			}
			$counter--;
		}
	}

	function print_space($count)
	{
		$html = '';
		for ($i = 1; $i <= $count; $i++) {
			$html .= '      ';
		}
		return $html;
	}

	/**
 * profitloss method
 *
 * @return void
 */
	public function profitloss($download = NULL, $format = NULL, $startdate = NULL, $enddate = NULL) {
		// set page title
		$this->mPageTitle = lang('page_title_reports_profitloss');
		$this->data['title'] = lang('profit_loss_title');
		$this->data['subtitle'] = lang('profit_loss_subtitle');

		$only_opening = false;
		// $startdate = null;
		// $enddate = null;

		if ($download === 'download') {
			if ($startdate && $enddate) {
				$startdate = $this->functionscore->dateToSql($startdate);
				$this->data['startdate'] = $startdate;
				$enddate = $this->functionscore->dateToSql($enddate);
				$this->data['enddate'] = $enddate;
			} else if ($startdate) {
				$startdate = $this->functionscore->dateToSql($startdate);
				$this->data['startdate'] = $startdate;
			} else if ($enddate) {
				$enddate = $this->functionscore->dateToSql($enddate);
				$this->data['enddate'] = $enddate;
			}	
		} else {
			$this->data['startdate'] = NULL;
			$this->data['enddate'] = NULL;
		}

		if ($this->input->method() == 'post') {
			$this->data['options'] = true;
			if (!empty($this->input->post('opening'))) {
				$only_opening = true;
				/* Sub-title*/
				$this->data['subtitle'] = sprintf(lang('opening_profit_loss_as_on'), $this->functionscore->dateFromSql($this->mAccountSettings->fy_start));
			} else {
				if ($this->input->post('startdate')) {
					$startdate = $this->functionscore->dateToSql($this->input->post('startdate'));
					$this->data['start_date'] = $startdate;
					$this->data['startdate'] = $this->input->post('startdate');
					// $startdate = $this->functionscore->dateToSql($this->input->post('startdate'));
				}
				if ($this->input->post('enddate')) {
					$enddate = $this->functionscore->dateToSql($this->input->post('enddate'));
					$this->data['end_date'] = $enddate;
					$this->data['enddate'] = $this->input->post('enddate');
					// $enddate = $this->functionscore->dateToSql($this->input->post('enddate'));
				}
				if ( $this->input->post('startdate') && $this->input->post('enddate')) {
					$this->data['subtitle'] = sprintf(lang('profit_loss_from_to'),  $this->functionscore->dateFromSql($this->functionscore->dateToSql($this->input->post('startdate'))), $this->functionscore->dateFromSql($this->functionscore->dateToSql($this->input->post('enddate'))));
				}
				if ( $this->input->post('startdate')) {
					$this->data['subtitle'] = sprintf(lang('profit_loss_from'), $this->functionscore->dateFromSql($this->functionscore->dateToSql($this->input->post('startdate'))));

				}
				if ($this->input->post('enddate')) {
					$this->data['subtitle'] = sprintf(lang('profit_loss_from_to'), $this->functionscore->dateFromSql($this->mAccountSettings->fy_start), $this->functionscore->dateFromSql($this->functionscore->dateToSql($this->input->post('enddate'))));
				}
			}
		}else{
			$this->data['options'] = false;
			if ($download === 'download') {
				if ($startdate && $enddate) {
					$this->data['subtitle'] = sprintf(lang('balance_sheet_from_to'), $this->functionscore->dateFromSql($this->functionscore->dateToSql($startdate)), $this->functionscore->dateFromSql($this->functionscore->dateToSql($enddate)));
				} else if ($startdate) {
					$this->data['subtitle'] = sprintf(lang('balance_sheet_from'), $this->functionscore->dateFromSql($this->functionscore->dateToSql($startdate)));
				} else if ($enddate) {
					$this->data['subtitle'] = sprintf(lang('balance_sheet_from_to'), $this->functionscore->dateFromSql($this->mAccountSettings->fy_start), $this->functionscore->dateFromSql($this->functionscore->dateToSql($enddate)));
				} else {
					/* Sub-title*/
					$this->data['subtitle'] = sprintf(lang('closing_balance_sheet_as_on'), $this->functionscore->dateFromSql($this->mAccountSettings->fy_end));
				}
			} else {
				/* Sub-title*/
				$this->data['subtitle'] = sprintf(lang('closing_balance_sheet_as_on'), $this->functionscore->dateFromSql($this->mAccountSettings->fy_end));
			}
		}


		/**********************************************************************/
		/*********************** GROSS CALCULATIONS ***************************/
		/**********************************************************************/
		$this->load->library('AccountList');
		/* Gross P/L : Expenses */
		$gross_expenses = new AccountList();
		$gross_expenses->Group = &$this->Group;
		$gross_expenses->Ledger = &$this->Ledger;
		$gross_expenses->only_opening = $only_opening;
		$gross_expenses->start_date = $startdate;
		$gross_expenses->end_date = $enddate;
		$gross_expenses->affects_gross = 1;
		$gross_expenses->start(4);

		$pandl['gross_expenses'] = $gross_expenses;

		$pandl['gross_expense_total'] = 0;
		if ($gross_expenses->cl_total_dc == 'D') {
			$pandl['gross_expense_total'] = $gross_expenses->cl_total;
		} else {
			$pandl['gross_expense_total'] = $this->functionscore->calculate($gross_expenses->cl_total, 0, 'n');
		}

		/* Gross P/L : Incomes */
		$gross_incomes = new AccountList();
		$gross_incomes->Group = &$this->Group;
		$gross_incomes->Ledger = &$this->Ledger;
		$gross_incomes->only_opening = $only_opening;
		$gross_incomes->start_date = $startdate;
		$gross_incomes->end_date = $enddate;
		$gross_incomes->affects_gross = 1;
		$gross_incomes->start(3);

		$pandl['gross_incomes'] = $gross_incomes;

		$pandl['gross_income_total'] = 0;
		if ($gross_incomes->cl_total_dc == 'C') {
			$pandl['gross_income_total'] = $gross_incomes->cl_total;
		} else {
			$pandl['gross_income_total'] = $this->functionscore->calculate($gross_incomes->cl_total, 0, 'n');
		}

		/* Calculating Gross P/L */
		$pandl['gross_pl'] = $this->functionscore->calculate($pandl['gross_income_total'], $pandl['gross_expense_total'], '-');

		/**********************************************************************/
		/************************* NET CALCULATIONS ***************************/
		/**********************************************************************/

		/* Net P/L : Expenses */
		$net_expenses = new AccountList();
		$net_expenses->Group = &$this->Group;
		$net_expenses->Ledger = &$this->Ledger;
		$net_expenses->only_opening = $only_opening;
		$net_expenses->start_date = $startdate;
		$net_expenses->end_date = $enddate;
		$net_expenses->affects_gross = 0;
		$net_expenses->start(4);

		$pandl['net_expenses'] = $net_expenses;

		$pandl['net_expense_total'] = 0;
		if ($net_expenses->cl_total_dc == 'D') {
			$pandl['net_expense_total'] = $net_expenses->cl_total;
		} else {
			$pandl['net_expense_total'] = $this->functionscore->calculate($net_expenses->cl_total, 0, 'n');
		}

		/* Net P/L : Incomes */
		$net_incomes = new AccountList();
		$net_incomes->Group = &$this->Group;
		$net_incomes->Ledger = &$this->Ledger;
		$net_incomes->only_opening = $only_opening;
		$net_incomes->start_date = $startdate;
		$net_incomes->end_date = $enddate;
		$net_incomes->affects_gross = 0;
		$net_incomes->start(3);

		$pandl['net_incomes'] = $net_incomes;

		$pandl['net_income_total'] = 0;
		if ($net_incomes->cl_total_dc == 'C') {
			$pandl['net_income_total'] = $net_incomes->cl_total;
		} else {
			$pandl['net_income_total'] = $this->functionscore->calculate($net_incomes->cl_total, 0, 'n');
		}

		/* Calculating Net P/L */
		$pandl['net_pl'] = $this->functionscore->calculate($pandl['net_income_total'], $pandl['net_expense_total'], '-');
		$pandl['net_pl'] = $this->functionscore->calculate($pandl['net_pl'], $pandl['gross_pl'], '+');

		$this->data['pandl'] = $pandl;

		if (!$download) {
			// render page
			$this->render('reports/profitloss');
		}

		if ($download === 'download') {
			if ($format === 'pdf') {
				$name = 'Profit&Loss.pdf';

				// $this->load->view('reports/pdf/profitloss', $this->data);
				
	            $html = $this->load->view('reports/pdf/profitloss', $this->data, TRUE, NULL, NULL, NULL, NULL, 'L');
	            $this->functionscore->generate_pdf($html, $name);
			}

			if ($format === 'csv') {
				$name = 'Profit&Loss.csv';
	            $html = $this->load->view('reports/downloadcsv/profitloss', $this->data, TRUE, NULL, NULL, NULL, NULL, 'L');
	            header('Content-Type: application/csv');
            	header('Content-Disposition: attachement; filename="' . $name . '"');
            	echo $html;
			}			
		}
		return;
	}
	/**
	 * trialbalance method
	 *
	 * @return void
	 */
	public function trialbalance($download = NULL, $format = NULL) {
		// set page title
		$this->mPageTitle = lang('page_title_reports_trialbalance');

		$this->data['title'] = lang('page_title_reports_trialbalance');
		$this->data['subtitle'] = sprintf(lang('trial_balance_from_to'), $this->functionscore->dateFromSql($this->mAccountSettings->fy_start), $this->functionscore->dateFromSql($this->mAccountSettings->fy_end));

		$this->load->library('AccountList');
		$accountlist = new AccountList();
		$accountlist->Group = &$this->Group;
		$accountlist->Ledger = &$this->Ledger;
		$accountlist->only_opening = false;
		$accountlist->start_date = null;
		$accountlist->end_date = null;
		$accountlist->affects_gross = -1;

		$accountlist->start(0);
		$this->data['accountlist'] = $accountlist;

		if (!$download) {
			// render page
			$this->render('reports/trialbalance');
		}

		if ($download === 'download') {
			if ($format === 'pdf') {
				$name = 'trialbalance.pdf';

				$this->load->view('reports/pdf/trialbalance', $this->data);

	            // $html = $this->load->view('reports/pdf/trialbalance', $this->data, TRUE, NULL, NULL, NULL, NULL, 'L');
	            // $this->functionscore->generate_pdf($html, $name);
			}	
			if ($format === 'csv') {
				$name = 'trialbalance.csv';
	            $html = $this->load->view('reports/downloadcsv/trialbalance', $this->data, TRUE, NULL, NULL, NULL, NULL, 'L');
	            header('Content-Type: application/csv');
            	header('Content-Disposition: attachement; filename="' . $name . '"');
            	echo $html;
			}			
		}
		
	}
	/**
	 * ledgerstatement method
	 *
	 * @return void
	 */
	public function ledgerstatement($show = true, $ledgerId = NULL) {
		// set page title
		$this->mPageTitle = lang('page_title_reports_ledgerstatement');
		$this->data['title'] = lang('page_title_reports_ledgerstatement');

		/* Create list of ledgers to pass to view */
		$ledgers = new LedgerTree();
		$ledgers->Group = &$this->Group;
		$ledgers->Ledger = &$this->Ledger;
		$ledgers->current_id = -1;
		$ledgers->restriction_bankcash = 1;
		$ledgers->build(0);
		$ledgers->toList($ledgers, -1);
		
		$this->data['ledgers'] = $ledgers->ledgerList;
		$this->data['showEntries'] = false;
		$this->data['options'] = false;

		if ($this->input->method() == 'post') {
			if (empty($this->input->post('ledger_id'))) {
				$this->session->set_flashdata('error', lang('invalid_ledger'));
				admin_redirect('areports/ledgerstatement');
			}
			$ledgerId = $this->input->post('ledger_id');
		}

		if ($ledgerId) {
			/* Check if ledger exists */
			$this->db->where('id', $ledgerId);
			$ledger = $this->db->get('sma_accounts_ledgers')->row_array();

			if (!$ledger) {
				$this->session->set_flashdata('error', lang('ledger_not_found'));
				admin_redirect('areports/ledgerstatement');
			}

			$this->data['ledger_data'] = $ledger;

			/* Set the approprite search conditions */
			$conditions = array();
			$conditions['sma_accounts_entryitems.ledger_id'] = $ledgerId;

			/* Set the approprite search conditions if custom date is selected */
			$startdate = null;
			$enddate = null;
		}

		
		if ($ledgerId) {
			$this->data['options'] = true;
			if (!empty($this->input->post('startdate'))) {
				/* TODO : Validate date */
				$startdate = $this->functionscore->dateToSql($this->input->post('startdate'));
				$conditions['sma_accounts_entries.date >='] = $startdate;
			}

			if (!empty($this->input->post('enddate'))) {
				/* TODO : Validate date */
				$enddate = $this->functionscore->dateToSql($this->input->post('enddate'));
				$conditions['sma_accounts_entries.date <='] = $enddate;
			}
			
			
			/* Sub-title*/
			if (!empty($this->input->post('startdate')) && !empty($this->input->post('enddate'))) {
				$this->data['subtitle'] = sprintf(lang('ledger_statement_from_to'),
					($ledger['name']),
					$this->functionscore->dateFromSql($this->functionscore->dateToSql($this->input->post('startdate'))),
					$this->functionscore->dateFromSql($this->functionscore->dateToSql($this->input->post('enddate')))
				);
			} else if (!empty($this->input->post('startdate'))) {
				$this->data['subtitle'] = sprintf(lang('ledger_statement_from_to'),
					($ledger['name']),
					$this->functionscore->dateFromSql($this->functionscore->dateToSql($this->input->post('startdate'))),
					$this->functionscore->dateFromSql($this->mAccountSettings->fy_end)
				);
			} else if (!empty($this->input->post('enddate'))) {
				$this->data['subtitle'] = sprintf(lang('ledger_statement_from_to'),
					($ledger['name']),
					$this->functionscore->dateFromSql($this->mAccountSettings->fy_start),
					$this->functionscore->dateFromSql($this->input->post('enddate'))
				);
			}else{
				$this->data['subtitle'] = sprintf(lang('ledger_statement_from_to'),
					($this->functionscore->toCodeWithName($ledger['code'], $ledger['name'])),
					$this->functionscore->dateFromSql($this->mAccountSettings->fy_start),
					$this->functionscore->dateFromSql($this->mAccountSettings->fy_end)
				);
				
			}
			/* Opening and closing titles */
			if (is_null($startdate)) {
				$this->data['opening_title'] = sprintf(lang('opening_balance_as_on'),
					$this->functionscore->dateFromSql($this->mAccountSettings->fy_start));
			} else {
				$this->data['opening_title'] = sprintf(lang('opening_balance_as_on'),
					$this->functionscore->dateFromSql($startdate));
			}
			if (is_null($enddate)) {
				$this->data['closing_title'] = sprintf(lang('closing_balance_as_on'),
					$this->functionscore->dateFromSql($this->mAccountSettings->fy_end));

			} else {
				$this->data['closing_title'] = sprintf(lang('closing_balance_as_on'),
					$this->functionscore->dateFromSql($enddate));
			}
			/* Calculating opening balance */
			$op = $this->ledger_model->openingBalance($ledgerId, $startdate);
			$this->data['op'] = $op;

			/* Calculating closing balance */
			$cl = $this->ledger_model->closingBalance($ledgerId, null, $enddate);
			$this->data['cl'] = $cl;

			/* Calculate current page opening balance */
			$current_op = $op;
			$this->db->where($conditions)->select('sma_accounts_entries.id, 	sma_accounts_entries.tag_id, 	sma_accounts_entries.entrytype_id, 	sma_accounts_entries.number, 	sma_accounts_entries.date, 	sma_accounts_entries.dr_total, 	sma_accounts_entries.cr_total, sma_accounts_entryitems.narration, sma_accounts_entryitems.entry_id, sma_accounts_entryitems.ledger_id, sma_accounts_entryitems.amount, sma_accounts_entryitems.dc, sma_accounts_entryitems.reconciliation_date')->join('sma_accounts_entryitems', 'sma_accounts_entries.id = sma_accounts_entryitems.entry_id', 'left')->order_by('sma_accounts_entries.date', 'asc');
			$this->data['entries'] = $this->db->get('sma_accounts_entries')->result_array();
			/* Set the current page opening balance */
			$this->data['current_op'] = $current_op;

			/* Pass varaibles to view which are used in Helpers */
			$this->data['allTags'] = $this->db->get('sma_accounts_tags')->result_array();
			$this->data['showEntries'] = true;
		}

		if ($show) {
			//$this->render('reports/ledgerstatement');
			$bc  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('areports/ledgerstatement'), 'page' => lang('Reports')], ['link' => '#', 'page' => lang('page_title_reports_ledgerstatement')]];
        $meta = ['page_title' => lang('page_title_reports_ledgerstatement'), 'bc' => $bc];
        $this->page_construct('accounts/reports_ledgerstatement', $meta, $this->data);

		}else{
			return array(
				'ledgers' 	=> $this->data['ledgers'],
				'showEntries' => $this->data['showEntries'],
				'ledger_data' => $this->data['ledger_data'],
				'subtitle' 	=> $this->data['subtitle'],
				'opening_title' => $this->data['opening_title'],
				'closing_title' => $this->data['closing_title'],
				'op' 			=> $this->data['op'],
				'cl' 			=> $this->data['cl'],
				'entries'		=> $this->data['entries'],
				'current_op' 	=> $this->data['current_op'],
				'allTags' 	=> $this->data['allTags'],
			);
		}
	}
	/**
 * ledgerentries method
 *
 * @return void
 */
	public function ledgerentries($show = true, $ledgerId = NULL) {
		// set page title
		$this->mPageTitle = lang('page_title_reports_ledgerentries');
		$this->data['title'] = lang('page_title_reports_ledgerentries');

		/* Create list of ledgers to pass to view */
		$ledgers = new LedgerTree();
		$ledgers->Group = &$this->Group;
		$ledgers->Ledger = &$this->Ledger;
		$ledgers->current_id = -1;
		$ledgers->restriction_bankcash = 1;
		$ledgers->build(0);
		$ledgers->toList($ledgers, -1);
		
		$this->data['ledgers'] = $ledgers->ledgerList;

		if ($this->input->method() == 'post') {
			if (empty($this->input->post('ledger_id'))) {
				$this->session->set_flashdata('error', lang('invalid_ledger'));
				redirect('reports/ledgerentries');
			}
			$ledgerId = $this->input->post('ledger_id');
		}
		$this->data['showEntries'] = false;
		$this->data['options'] = false;

		
		if ($ledgerId) {
			/* Check if ledger exists */
			$this->db->where('id', $ledgerId);
			$ledger = $this->db->get('sma_accounts_ledgers')->row_array();

			if (!$ledger) {
				$this->session->set_flashdata('error', lang('ledger_not_found'));
				redirect('reports/ledgerentries');
			}

			$this->data['ledger_data'] = $ledger;


			/* Set the approprite search conditions */
			$conditions = array();
			$conditions['entryitems.ledger_id'] = $ledgerId;

			/* Set the approprite search conditions if custom date is selected */
			$startdate = null;
			$enddate = null;

			$this->data['options'] = true;

			if (!empty($this->input->post('startdate'))) {
				/* TODO : Validate date */
				$startdate = $this->functionscore->dateToSql($this->input->post('startdate'));
				$conditions['entries.date >='] = $startdate;
			}

			if (!empty($this->input->post('enddate'))) {
				/* TODO : Validate date */
				$enddate = $this->functionscore->dateToSql($this->input->post('enddate'));
				$conditions['entries.date <='] = $enddate;
			}
			
			
			/* Sub-title*/
			if (!empty($this->input->post('startdate')) && !empty($this->input->post('enddate'))) {
				$this->data['subtitle'] = sprintf(lang('ledger_entries_from_to'),
					($ledger['name']),
					$this->functionscore->dateFromSql($this->functionscore->dateToSql($this->input->post('startdate'))),
					$this->functionscore->dateFromSql($this->functionscore->dateToSql($this->input->post('enddate')))
				);
			} else if (!empty($this->input->post('startdate'))) {
				$this->data['subtitle'] = sprintf(lang('ledger_entries_from_to'),
					($ledger['name']),
					$this->functionscore->dateFromSql($this->functionscore->dateToSql($this->input->post('startdate'))),
					$this->functionscore->dateFromSql($this->mAccountSettings->fy_end)
				);
			} else if (!empty($this->input->post('enddate'))) {
				$this->data['subtitle'] = sprintf(lang('ledger_entries_from_to'),
					($ledger['name']),
					$this->functionscore->dateFromSql($this->mAccountSettings->fy_start),
					$this->functionscore->dateFromSql($this->input->post('enddate'))
				);
			}else{
				$this->data['subtitle'] = sprintf(lang('ledger_entries_from_to'),
					($this->functionscore->toCodeWithName($ledger['code'], $ledger['name'])),
					$this->functionscore->dateFromSql($this->mAccountSettings->fy_start),
					$this->functionscore->dateFromSql($this->mAccountSettings->fy_end)
				);
				
			}
			/* Opening and closing titles */
			if (is_null($startdate)) {
				$this->data['opening_title'] = sprintf(lang('opening_balance_as_on'),
					$this->functionscore->dateFromSql($this->mAccountSettings->fy_start));
			} else {
				$this->data['opening_title'] = sprintf(lang('opening_balance_as_on'),
					$this->functionscore->dateFromSql($startdate));
			}
			if (is_null($enddate)) {
				$this->data['closing_title'] = sprintf(lang('closing_balance_as_on'),
					$this->functionscore->dateFromSql($this->mAccountSettings->fy_end));

			} else {
				$this->data['closing_title'] = sprintf(lang('closing_balance_as_on'),
					$this->functionscore->dateFromSql($enddate));
			}
			/* Calculating opening balance */
			$op = $this->ledger_model->openingBalance($ledgerId, $startdate);
			$this->data['op'] = $op;

			/* Calculating closing balance */
			$cl = $this->ledger_model->closingBalance($ledgerId, null, $enddate);
			$this->data['cl'] = $cl;

			/* Calculate current page opening balance */
			$current_op = $op;
			$this->db->where($conditions)->select('entries.id, entries.tag_id, entries.entrytype_id, entries.number, entries.date, entries.dr_total, entries.cr_total, entryitems.narration, entryitems.entry_id, entryitems.ledger_id, entryitems.amount, entryitems.dc, entryitems.reconciliation_date')->join('entryitems', 'entries.id = entryitems.entry_id', 'left')->order_by('entries.date', 'asc');

			$this->data['entries'] = $this->db->get('entries')->result_array();
			/* Set the current page opening balance */
			$this->data['current_op'] = $current_op;

			/* Pass varaibles to view which are used in Helpers */
			$this->data['allTags'] = $this->db->get('tags')->result_array();
			$this->data['showEntries'] = true;
		}

		if ($show) {
			// render page
			$this->render('reports/ledgerentries');
		}else{
			return array(
				'ledgers' 	=> $this->data['ledgers'],
				'showEntries' => $this->data['showEntries'],
				'ledger_data' => $this->data['ledger_data'],
				'subtitle' 	=> $this->data['subtitle'],
				'opening_title' => $this->data['opening_title'],
				'closing_title' => $this->data['closing_title'],
				'op' 			=> $this->data['op'],
				'cl' 			=> $this->data['cl'],
				'entries'		=> $this->data['entries'],
				'current_op' 	=> $this->data['current_op'],
				'allTags' 	=> $this->data['allTags'],
			);
		}

	}

	/**
	 * reconciliation method
	 *
	 * @return void
	 */
	public function reconciliation($download = NULL, $format=NULL) {
		// set page title
		$this->mPageTitle = lang('page_title_reports_reconciliation');
		$this->data['title'] = lang('page_title_reports_reconciliation');
		

		/* Create list of ledgers to pass to view */
		$this->db->where('ledgers.reconciliation', 1);
		$this->db->order_by('ledgers.name', 'asc');
		$this->db->select('ledgers.id, ledgers.name, ledgers.code');
		$ledgers_q = $this->db->get('ledgers')->result_array();
		if ($ledgers_q) {
			$ledgers = array(0 => lang('please_select'));
			foreach ($ledgers_q as $row) {
				$ledgers[$row['id']] = $this->functionscore->toCodeWithName(
					$row['code'], $row['name']
				);
			}
		}else{
			$ledgers = array(0 => lang('no_reconciled_ledgers_found'));
		}
		$this->data['ledgers'] = $ledgers;

		if ($this->input->method() == 'post') {
			/* Ledger selection form submitted */
			if (!empty($this->input->post('submit_ledger'))) {
				if (empty($this->input->post('ledger_id'))) {
					$this->session->set_flashdata('error', lang('invalid_ledger'));
					redirect('reports/reconciliation');
				}
			} else if (!empty($this->input->post('submitrec'))) {
				/* Check if acccount is locked */
				if ($this->mAccountSettings->account_locked == 1) {
					$this->session->set_flashdata('error', lang('groups_cntrler_edit_account_locked_error'));
					redirect('reports/reconciliation');
				}

				/* Reconciliation form submitted */
				foreach ($this->input->post('ReportRec[]') as $row => $recitem) {
					if (empty($recitem['id'])) {
						continue;
					}
					if (!empty($recitem['recdate'])) {
						$recdate = $this->functionscore->dateToSql($recitem['recdate']);
						if (!$recdate) {
							$this->session->set_flashdata('error', lang('invalid_reconciliation_date'));
							continue;
						}
					} else {
						$recdate = NULL;
					}
					$this->db->where('id', $recitem['id']);
					$this->db->update('entryitems', array('reconciliation_date'=>$recdate));

				}
				$this->session->set_flashdata('message', lang('reconciliation_successs'));
				redirect('reports/reconciliation');
			} else {
				redirect('reports/reconciliation');
			}
		}

		$this->data['showEntries'] = false;
		$this->data['options'] = false;

		/* Set the approprite search conditions if custom date is selected */
		$startdate = null;
		$enddate = null;

		if ($this->input->method() == 'post') {
			$ledgerId = $this->input->post('ledger_id');

			/* Check if ledger exists */
			$this->db->where('id', $ledgerId);
			$ledger = $this->db->from('ledgers')->get()->row_array();

			if (!$ledger) {
				$this->session->set_flashdata('error', lang('ledger_not_found'));
				redirect('reports/reconciliation');
			}

			$this->data['ledger_data'] = $ledger;


			/* Set the approprite search conditions */
			$conditions = array();
			$conditions['entryitems.ledger_id'] = $ledgerId;
			$this->data['options'] = true;
			
			if (!empty($this->input->post('startdate'))) {
				/* TODO : Validate date */
				$startdate = $this->functionscore->dateToSql($this->input->post('startdate'));
				$conditions['entries.date >='] = $startdate;
			}

			if (!empty($this->input->post('enddate'))) {
				/* TODO : Validate date */
				$enddate = $this->functionscore->dateToSql($this->input->post('enddate'));
				$conditions['entries.date <='] = $enddate;
			}

			/* Sub-title*/
			if (!empty($this->input->post('startdate')) && !empty($this->input->post('enddate'))) {
				$this->data['subtitle'] = sprintf(lang('reconciliation_for_from_to'),
					($ledger['name']),
					$this->functionscore->dateFromSql($this->functionscore->dateToSql($this->input->post('startdate'))),
					$this->functionscore->dateFromSql($this->functionscore->dateToSql($this->input->post('enddate')))
				);
			} else if (!empty($this->input->post('startdate'))) {
				$this->data['subtitle'] = sprintf(lang('reconciliation_for_from_to'),
					($ledger['name']),
					$this->functionscore->dateFromSql($this->functionscore->dateToSql($this->input->post('startdate'))),
					$this->functionscore->dateFromSql($this->mAccountSettings->fy_end)
				);
			} else if (!empty($this->input->post('enddate'))) {
				$this->data['subtitle'] = sprintf(lang('reconciliation_for_from_to'),
					($ledger['name']),
					$this->functionscore->dateFromSql($this->mAccountSettings->fy_start),
					$this->functionscore->dateFromSql($this->input->post('enddate'))
				);
			}else{
				$this->data['subtitle'] = sprintf(lang('reconciliation_for_from_to'),
					($this->functionscore->toCodeWithName($ledger['code'], $ledger['name'])),
					$this->functionscore->dateFromSql($this->mAccountSettings->fy_start),
					$this->functionscore->dateFromSql($this->mAccountSettings->fy_end)
				);
			}

			if (empty($this->input->post('showall'))) {
				$conditions['entryitems.reconciliation_date'] = NULL;
			}
			/* Opening and closing titles */
			if (is_null($startdate)) {
				$this->data['opening_title'] = sprintf(lang('opening_balance_as_on'),
					$this->functionscore->dateFromSql($this->mAccountSettings->fy_start));
			} else {
				$this->data['opening_title'] = sprintf(lang('opening_balance_as_on'),
					$this->functionscore->dateFromSql($startdate));
			}
			if (is_null($enddate)) {
				$this->data['closing_title'] = sprintf(lang('closing_balance_as_on'),
					$this->functionscore->dateFromSql($this->mAccountSettings->fy_end));

			} else {
				$this->data['closing_title'] = sprintf(lang('closing_balance_as_on'),
					$this->functionscore->dateFromSql($enddate));
			}

			/* Reconciliation pending title */
			$this->data['recpending_title'] = '';

			/* Sub-title*/
			if (!is_null($startdate) && !is_null($enddate)) {
				$this->data['recpending_title'] = sprintf(lang('reconciliation_from_to'),
					$this->functionscore->dateFromSql($this->functionscore->dateToSql($this->input->post('startdate'))),
					$this->functionscore->dateFromSql($this->functionscore->dateToSql($this->input->post('enddate')))
				);
			} else if (!is_null($this->input->post('startdate'))) {
				$this->data['recpending_title'] = sprintf(lang('reconciliation_from_to'),
					$this->functionscore->dateFromSql($this->functionscore->dateToSql($this->input->post('startdate'))),
					$this->functionscore->dateFromSql($this->mAccountSettings->fy_end)
				);
			} else if (is_null($this->input->post('enddate'))) {
				$this->data['recpending_title'] = sprintf(lang('reconciliation_from_to'),
					$this->functionscore->dateFromSql($this->mAccountSettings->fy_start),
					$this->functionscore->dateFromSql($this->input->post('enddate'))
				);
			}else{
				$this->data['recpending_title'] = sprintf(lang('reconciliation_from_to'),
					$this->functionscore->dateFromSql($this->mAccountSettings->fy_start),
					$this->functionscore->dateFromSql($this->mAccountSettings->fy_end)
				);
			}
			
			/* Calculating opening balance */
			$op = $this->ledger_model->openingBalance($ledgerId, $startdate);
			$this->data['op'] = $op;

			/* Calculating closing balance */
			$cl = $this->ledger_model->closingBalance($ledgerId, null, $enddate);
			$this->data['cl'] = $cl;

			/* Calculating reconciliation pending balance */
			$rp = $this->ledger_model->reconciliationPending($ledgerId, $startdate, $enddate);
			$this->data['rp'] = $rp;

			$this->db->where($conditions)->select('entryitems.id as eiid, entries.id , entries.tag_id, entries.entrytype_id, entries.number, entries.date, entries.dr_total, entries.cr_total, entryitems.narration, entryitems.entry_id, entryitems.ledger_id, entryitems.amount, entryitems.dc, entryitems.reconciliation_date')->join('entryitems', 'entries.id = entryitems.entry_id', 'left')->order_by('entries.date', 'asc');
			$this->data['entries'] = $this->db->get('entries')->result_array();

			/* Pass varaibles to view which are used in Helpers */
			$this->data['allTags'] = $this->db->get('tags')->result_array();
			$this->data['showEntries'] = true;

		}

		// render page
		$this->render('reports/reconciliation');

	}

	// Export Functions
	public function export_ledgerstatement($type = 'xls', $id)
	{
		$data = $this->ledgerstatement(false, $id);
		extract($data);
        if ($showEntries) {
            $this->load->library('excel');
            $this->excel->setActiveSheetIndex(0);
            if ($type=='pdf') {
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );
                $this->excel->getDefaultStyle()->applyFromArray($styleArray);
            }
			
            $this->excel->getActiveSheet()->setTitle(lang('sidebar_menu_reports_child_ledgerstatement'));

            $this->excel->getActiveSheet()->SetCellValue('A1', $subtitle);
            $this->excel->getActiveSheet()->mergeCells('A1:H1');


           $this->excel->getActiveSheet()->SetCellValue('A2', lang('ledgers_views_add_label_bank_cash_account'));
            $this->excel->getActiveSheet()->mergeCells('A2:B2');
			$this->excel->getActiveSheet()->SetCellValue('A3', lang('ledgers_views_add_label_notes'));

            $this->excel->getActiveSheet()->SetCellValue('C2', ($ledger_data['type'] == 1) ? lang('yes') : lang('no'));
            $this->excel->getActiveSheet()->SetCellValue('C3', $ledger_data['notes']);


            $this->excel->getActiveSheet()->SetCellValue('E2', $opening_title);
            $this->excel->getActiveSheet()->mergeCells('E2:G2');

            $this->excel->getActiveSheet()->SetCellValue('H2', $this->functionscore->toCurrency($op['dc'], $op['amount']));
            $this->excel->getActiveSheet()->SetCellValue('E3', $closing_title);
            $this->excel->getActiveSheet()->mergeCells('E3:G3');

            $this->excel->getActiveSheet()->SetCellValue('H3', $this->functionscore->toCurrency($cl['dc'], $cl['amount']));


            $this->excel->getActiveSheet()->SetCellValue('A5', lang('entries_views_add_label_date'));
            $this->excel->getActiveSheet()->SetCellValue('B5', lang('entries_views_add_label_number'));
            $this->excel->getActiveSheet()->SetCellValue('C5', lang('entries_views_add_items_th_ledger'));
            $this->excel->getActiveSheet()->SetCellValue('D5', lang('accounts_index_type'));
            $this->excel->getActiveSheet()->SetCellValue('E5', lang('entries_views_add_label_tag') );
            $this->excel->getActiveSheet()->SetCellValue('F5', lang('entries_views_add_items_th_dr_amount') );
            $this->excel->getActiveSheet()->SetCellValue('G5', lang('entries_views_add_items_th_cr_amount') );
            $this->excel->getActiveSheet()->SetCellValue('H5', lang('balance') );

            $entry_balance['amount'] = $current_op['amount'];
			$entry_balance['dc'] = $current_op['dc'];

		 	$this->excel->getActiveSheet()->SetCellValue('A6', lang('curr_opening_balance'));
            $this->excel->getActiveSheet()->mergeCells('A6:G6');
            $this->excel->getActiveSheet()->SetCellValue('H6', $this->functionscore->toCurrency($current_op['dc'], $current_op['amount']));
			
            $row = 7;
            foreach ($entries as $entry) {
                $ir = $row + 1;
                if ($ir % 2 == 0) {
                    $style_header = array(                  
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb'=>'CCCCCC'),
                        ),
                    );
                    $this->excel->getActiveSheet()->getStyle("A$row:H$row")->applyFromArray( $style_header );
                }
				/* Calculate current entry balance */
				$entry_balance = $this->functionscore->calculate_withdc(
					$entry_balance['amount'], $entry_balance['dc'],
					$entry['amount'], $entry['dc']
				);

				$et = $this->db->where('id', $entry['entrytype_id'])->get('entrytypes')->row_array();
				$entryTypeName = $et['name'];
				$entryTypeLabel = $et['label'];


                $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->functionscore->dateFromSql($entry['date']));
                $this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->functionscore->toEntryNumber($entry['number'], $entry['entrytype_id']));
                $this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->functionscore->entryLedgers($entry['id']));
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $entryTypeName);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->settings_model->getTagNameByID($entry['tag_id']));
                
                if ($entry['dc'] == 'D') {
                	$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->functionscore->toCurrency('D', $entry['amount']));
				} else if ($entry['dc'] == 'C') {
                	$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->functionscore->toCurrency('C', $entry['amount']));
				} else {
                	$this->excel->getActiveSheet()->SetCellValue('F' . $row, lang('search_views_amounts_td_error'));
                	$this->excel->getActiveSheet()->SetCellValue('G' . $row, lang('search_views_amounts_td_error'));
				}

                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->functionscore->toCurrency($entry_balance['dc'], $entry_balance['amount']));
                $row++;
            }
            $style_header = array(                  
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb'=>'fdbf2d'),
                ),
            );


            $this->excel->getActiveSheet()->getStyle("A$row:H$row")->applyFromArray( $style_header );
            $this->excel->getActiveSheet()->getStyle("A6:H6")->applyFromArray( $style_header );


		 	$this->excel->getActiveSheet()->SetCellValue("A$row", lang('curr_closing_balance'));
            $this->excel->getActiveSheet()->mergeCells("A$row:G$row");
            $this->excel->getActiveSheet()->SetCellValue("H$row", $this->functionscore->toCurrency($entry_balance['dc'], $entry_balance['amount']));


            $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
            $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
            $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(60);
            $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
            $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
            $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
            $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
            $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
           
            $filename = 'ledgerstatement';
            $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        
            $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

            $header = 'A1:H1';
            $this->excel->getActiveSheet()->getStyle($header)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('94ce58');
            $style = array(
                'font' => array('bold' => true,),
                'alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,),
            );
            $this->excel->getActiveSheet()->getStyle($header)->applyFromArray($style);
            
            $titles = 'A5:H5';
            $this->excel->getActiveSheet()->getStyle($titles)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('94ce58');
            $style = array(
                'font' => array('bold' => true,),
                'alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,),
            );
            $this->excel->getActiveSheet()->getStyle($titles)->applyFromArray($style);
            

            $header = 'A2:H3';
            $this->excel->getActiveSheet()->getStyle($header)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('fdbf2d');
            $style = array(
                'font' => array('bold' => true,),
            );
            $this->excel->getActiveSheet()->getStyle($header)->applyFromArray($style);


            if ($type=='pdf') {
                require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDFF" . DIRECTORY_SEPARATOR . "mpdf.php");
                $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                $rendererLibrary = 'MPDFF';
                $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                    die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                        PHP_EOL . ' as appropriate for your directory structure');
                }

                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                header('Cache-Control: max-age=0');

                $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                $objWriter->save('php://output');
                exit();
            }
            if ($type=='xls') {
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                header('Cache-Control: max-age=0');
                $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                $objWriter->save('php://output');
                exit();
            }
        }
	}
	public function export_ledgerentries($type = 'xls', $id)
	{
		$data = $this->ledgerentries(false, $id);
		extract($data);
        if ($showEntries) {
            $this->load->library('excel');
            $this->excel->setActiveSheetIndex(0);
            if ($type=='pdf') {
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );
                $this->excel->getDefaultStyle()->applyFromArray($styleArray);
            }
			
            $this->excel->getActiveSheet()->setTitle(lang('sidebar_menu_reports_child_ledgerentries'));

            $this->excel->getActiveSheet()->SetCellValue('A1', $subtitle);
            $this->excel->getActiveSheet()->mergeCells('A1:H1');


            $this->excel->getActiveSheet()->SetCellValue('A2', lang('ledgers_views_add_label_bank_cash_account'));
            $this->excel->getActiveSheet()->mergeCells('A2:B2');
			$this->excel->getActiveSheet()->SetCellValue('A3', lang('ledgers_views_add_label_notes'));
            $this->excel->getActiveSheet()->mergeCells('A3:B3');

            $this->excel->getActiveSheet()->SetCellValue('C2', ($ledger_data['type'] == 1) ? lang('yes') : lang('no'));
            $this->excel->getActiveSheet()->SetCellValue('C3', $ledger_data['notes']);


            $this->excel->getActiveSheet()->SetCellValue('E2', $opening_title);
            $this->excel->getActiveSheet()->mergeCells('E2:G2');

            $this->excel->getActiveSheet()->SetCellValue('H2', $this->functionscore->toCurrency($op['dc'], $op['amount']));
            $this->excel->getActiveSheet()->SetCellValue('E3', $closing_title);
            $this->excel->getActiveSheet()->mergeCells('E3:G3');

            $this->excel->getActiveSheet()->SetCellValue('H3', $this->functionscore->toCurrency($cl['dc'], $cl['amount']));


            $this->excel->getActiveSheet()->SetCellValue('A5', lang('entries_views_add_label_date'));
            $this->excel->getActiveSheet()->SetCellValue('B5', lang('entries_views_add_label_number'));
            $this->excel->getActiveSheet()->SetCellValue('C5', lang('entries_views_add_items_th_ledger'));
            $this->excel->getActiveSheet()->SetCellValue('D5', lang('accounts_index_type'));
            $this->excel->getActiveSheet()->SetCellValue('E5', lang('entries_views_add_label_tag') );
            $this->excel->getActiveSheet()->SetCellValue('F5', lang('entries_views_add_items_th_dr_amount') );
            $this->excel->getActiveSheet()->SetCellValue('G5', lang('entries_views_add_items_th_cr_amount') );
            $this->excel->getActiveSheet()->SetCellValue('H5', lang('balance') );

            $entry_balance['amount'] = $current_op['amount'];
			$entry_balance['dc'] = $current_op['dc'];

		 
            $row = 6;
            foreach ($entries as $entry) {
                $ir = $row + 1;
                if ($ir % 2 == 0) {
                    $style_header = array(                  
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb'=>'CCCCCC'),
                        ),
                    );
                    $this->excel->getActiveSheet()->getStyle("A$row:H$row")->applyFromArray( $style_header );
                }
				/* Calculate current entry balance */
				$entry_balance = $this->functionscore->calculate_withdc(
					$entry_balance['amount'], $entry_balance['dc'],
					$entry['amount'], $entry['dc']
				);

				$et = $this->db->where('id', $entry['entrytype_id'])->get('entrytypes')->row_array();
				$entryTypeName = $et['name'];
				$entryTypeLabel = $et['label'];


                $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->functionscore->dateFromSql($entry['date']));
                $this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->functionscore->toEntryNumber($entry['number'], $entry['entrytype_id']));
                $this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->functionscore->entryLedgers($entry['id']));
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $entryTypeName);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->settings_model->getTagNameByID($entry['tag_id']));
                
                if ($entry['dc'] == 'D') {
                	$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->functionscore->toCurrency('D', $entry['amount']));
				} else if ($entry['dc'] == 'C') {
                	$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->functionscore->toCurrency('C', $entry['amount']));
				} else {
                	$this->excel->getActiveSheet()->SetCellValue('F' . $row, lang('search_views_amounts_td_error'));
                	$this->excel->getActiveSheet()->SetCellValue('G' . $row, lang('search_views_amounts_td_error'));
				}

                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->functionscore->toCurrency($entry_balance['dc'], $entry_balance['amount']));
                $row++;
            }
            $style_header = array(                  
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb'=>'fdbf2d'),
                ),
            );


            $this->excel->getActiveSheet()->getStyle("A$row:H$row")->applyFromArray( $style_header );
            $this->excel->getActiveSheet()->getStyle("A6:H6")->applyFromArray( $style_header );


            $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
            $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
            $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(60);
            $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
            $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
            $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
            $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
            $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
           
            $filename = 'ledgerentries';
            $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        
            $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

            $header = 'A1:H1';
            $this->excel->getActiveSheet()->getStyle($header)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('94ce58');
            $style = array(
                'font' => array('bold' => true,),
                'alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,),
            );
            $this->excel->getActiveSheet()->getStyle($header)->applyFromArray($style);
            
            $titles = 'A5:H5';
            $this->excel->getActiveSheet()->getStyle($titles)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('94ce58');
            $style = array(
                'font' => array('bold' => true,),
                'alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,),
            );
            $this->excel->getActiveSheet()->getStyle($titles)->applyFromArray($style);
            

            $header = 'A2:H3';
            $this->excel->getActiveSheet()->getStyle($header)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('fdbf2d');
            $style = array(
                'font' => array('bold' => true,),
            );
            $this->excel->getActiveSheet()->getStyle($header)->applyFromArray($style);


            if ($type=='pdf') {
                require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDFF" . DIRECTORY_SEPARATOR . "mpdf.php");
                $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                $rendererLibrary = 'MPDFF';
                $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                    die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                        PHP_EOL . ' as appropriate for your directory structure');
                }

                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                header('Cache-Control: max-age=0');

                $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                $objWriter->save('php://output');
                exit();
            }
            if ($type=='xls') {
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                header('Cache-Control: max-age=0');
                $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                $objWriter->save('php://output');
                exit();
            }
        }
	}



	public function statement_cashflow() {
		// set page title
		$this->mPageTitle = 'Statement of Cashflow';
		$this->data['title'] = 'Statement of Cashflow';


	}
	

}