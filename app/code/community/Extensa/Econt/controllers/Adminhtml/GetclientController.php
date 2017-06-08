<?php
/**
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_Adminhtml_GetclientController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $data = array(
            'type' => 'access_clients',
        );

        $results_data = array();

        $results = Mage::helper('extensa_econt')->serviceTool($data);

        if ($results) {
            if (isset($results->error)) {
                $results_data['error'] = true;
                $results_data['message'] = (string)$results->error->message;
            } else {
                if (isset($results->clients)) {
                    foreach ($results->clients->client as $client) {
                        $results_data['key_words'][] = (string)$client->key_word;

                        if (isset($client->cd_agreements)) {
                            foreach ($client->cd_agreements->cd_agreement as $cd_agreement) {
                                $results_data['cd_agreement_nums'][] = (string)$cd_agreement->num;
                            }
                        }

                        if (isset($client->instructions)) {
                            foreach ($client->instructions->e as $instruction) {
                                $results_data['instructions'][(string)$instruction->type][] = (string)$instruction->template;
                            }
                        }
                    }
                }
            }
        } else {
            $results_data['error'] = true;
            $results_data['message'] = Mage::helper('extensa_econt')->__('Възникна грешка при връзката с Еконт! Моля, опитайте отново!');
        }

        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode($results_data)
        );
    }
}
