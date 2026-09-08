<?php

namespace chgold\AIConnectAdmin;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;

class Setup extends AbstractSetup
{
    use StepRunnerInstallTrait;
    use StepRunnerUninstallTrait;
    use StepRunnerUpgradeTrait;

    /**
     * Makes the 'admin' OAuth scope requestable.
     *
     * Every registered client ships allowing only read and write, which is why
     * the administrative tools could never be called: the scope they demand was
     * not obtainable by any client, under any circumstances. Installing this
     * add-on is the explicit act that opens it.
     *
     * Adding the scope does NOT hand it out. A client still has to ask for it
     * and the member still has to approve it on the consent screen, and every
     * tool then re-checks that the connected account is genuinely an
     * administrator. Uninstalling withdraws the scope again.
     */
    public function installStep1(): void
    {
        $this->addAdminScopeToClients();
    }

    public function upgrade1000000Step1(): void
    {
        $this->addAdminScopeToClients();
    }

    public function uninstallStep1(): void
    {
        $db = $this->db();

        $clients = $db->fetchAll('SELECT client_id, allowed_scopes FROM xf_ai_connect_oauth_clients');
        foreach ($clients as $client) {
            $scopes = json_decode((string) $client['allowed_scopes'], true);
            if (!is_array($scopes) || !in_array('admin', $scopes, true)) {
                continue;
            }

            $scopes = array_values(array_diff($scopes, ['admin']));
            $db->update(
                'xf_ai_connect_oauth_clients',
                ['allowed_scopes' => json_encode($scopes)],
                'client_id = ?',
                $client['client_id']
            );
        }

        // Tokens already carrying the scope must lose their power too, otherwise
        // uninstalling would leave live admin-capable tokens behind.
        $db->query(
            "UPDATE xf_ai_connect_oauth_tokens
                SET revoked_date = ?
              WHERE revoked_date = 0
                AND scopes LIKE '%admin%'",
            [\XF::$time]
        );
    }

    protected function addAdminScopeToClients(): void
    {
        $db = $this->db();

        $clients = $db->fetchAll('SELECT client_id, allowed_scopes FROM xf_ai_connect_oauth_clients');
        foreach ($clients as $client) {
            $scopes = json_decode((string) $client['allowed_scopes'], true);
            if (!is_array($scopes)) {
                $scopes = array_filter(array_map('trim', explode(',', (string) $client['allowed_scopes'])));
            }
            if (in_array('admin', $scopes, true)) {
                continue;
            }

            $scopes[] = 'admin';
            $db->update(
                'xf_ai_connect_oauth_clients',
                ['allowed_scopes' => json_encode(array_values($scopes))],
                'client_id = ?',
                $client['client_id']
            );
        }
    }
}
