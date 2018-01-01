# tr_top_ratter
Wordpress plugin  for eve online that uses SSO login and ESI api functionality. This is work in progress.
Plugin gathers tax from the bounty_prizes incomes of the journal records for corporation. Calls ESI api and acquires the journal data to be processed and displayed.

User logs in with its Eve Online characters using SSO login and these characters are attached to the user. User then can choose main characters. All data is displayed as summ of other user attached characters for pvp kills and ratting report.

Plugin also uses Zkilboard xml API to get the kills for the corporation by month.

Plugin has options to enable/disable certain functions like pvp stats, charts or ratting systems, but the core functionality of showing the data table can not be disabled, otherwise whats the point of plugin?

# In development
- Structures incomes. This should show how much incomes does the corporation get from all its structures.
- Character bacground check. This should be available to the administrators/directors and should show bacground information about the character within the defined scopes.
- Mining ledger. Something something mining rocks and showing stuff. It has not yet been decided.


# Scopes. 
publicData characterLocationRead characterSkillsRead characterAccountRead corporationWalletRead corporationAssetsRead 
corporationKillsRead esi-location.read_location.v1 esi-location.read_ship_type.v1 esi-mail.read_mail.v1 esi-
skills.read_skills.v1 esi-skills.read_skillqueue.v1 esi-wallet.read_character_wallet.v1 esi-wallet.read_corporation_wallet.v1 
esi-characters.read_contacts.v1 esi-assets.read_assets.v1 esi-industry.read_character_jobs.v1 esi-
characters.read_corporation_roles.v1 esi-location.read_online.v1 esi-contracts.read_character_contracts.v1 esi-
killmails.read_corporation_killmails.v1 esi-wallet.read_corporation_wallets.v1 esi-industry.read_character_mining.v1 esi-
industry.read_corporation_mining.v1

# Endpoints used by ESI api
- Some endpoints might change or additional endpoints might be used to get the related additional data.

/v2/characters/{character_id}/assets/
/characters/{character_id}/contacts/
/characters/{character_id}/contracts/
/characters/{character_id}/contracts/{contract_id}/items/
/characters/{character_id}/industry/jobs/
/characters/{character_id}/mining/
/corporation/{corporation_id}/mining/observers/
/corporation/{corporation_id}/mining/extractions/
/characters/{character_id}/location/
/characters/{character_id}/ship/
/characters/{character_id}/online/
/characters/{character_id}/mail/
/characters/{character_id}/skillqueue/
/characters/{character_id}/skills/
/characters/{character_id}/wallet/
/characters/{character_id}/wallet/journal/
/characters/{character_id}/wallet/transactions/
