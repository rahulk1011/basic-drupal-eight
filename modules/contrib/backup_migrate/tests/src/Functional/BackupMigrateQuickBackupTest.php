<?php

namespace Drupal\Tests\backup_migrate\Functional;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests backup migrate quick backup functionality.
 *
 * @group backup_migrate
 */
class BackupMigrateQuickBackupTest extends BrowserTestBase {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['backup_migrate'];

  /**
   * {@inheritdoc}
   */
  protected $strictConfigSchema = FALSE;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Ensure backup_migrate folder exists.
    $path = 'private://backup_migrate/';
    \Drupal::service('file_system')
      ->prepareDirectory($path, FileSystemInterface::CREATE_DIRECTORY);

    // Log in as an admin.
    $this->drupalLogin($this->drupalCreateUser([
      'perform backup',
      'access backup files',
      'administer backup and migrate',
      // Required for the file admin page.
      'administer site configuration',
    ]));
    $this->drupalGet('admin/config/media/file-system');
  }

  /**
   * Tests quick backup.
   */
  public function testQuickBackup() {
    // Load the main B&M admin page.
    $this->drupalGet('admin/config/development/backup_migrate');
    $this->assertSession()->statusCodeEquals(200);

    // Submit the quick backup form.
    $data = [
      'source_id' => 'default_db',
      'destination_id' => 'private_files',
    ];
    $this->submitForm($data, $this->t('Backup now'));

    // Confirm that the form submitted.
    $this->assertSession()->pageTextContains('Backup Complete.');

    // Get backups page.
    $this->drupalGet('admin/config/development/backup_migrate/backups');
    $this->assertSession()->statusCodeEquals(200);

    // Searching for the existing backups.
    $page = $this->getSession()->getPage();
    $table = $page->find('css', 'table');
    $row = $table->find('css', sprintf('tbody tr:contains("%s")', '.mysql.gz'));
    $this->assertNotNull($row);
  }

  /**
   * Verify that backups can be restored.
   */
  public function testBackupsCanBeRestored() {
    $this->testQuickBackup();

    // Load the destination page for the private files destination.
    $this->drupalGet('admin/config/development/backup_migrate/settings/destination/backups/private_files');
    $session = $this->assertSession();
    $session->statusCodeEquals(200);

    // Confirm a file exists with a "restore" link.
    $session->linkExists('Restore');

    // Load the route for deleting an existing backup.
    $this->clickLink('Restore');
    $session = $this->assertSession();
    $session->statusCodeEquals(200);
    $session->pageTextContains('Are you sure you want to restore this backup?');

    // Restore the backup.
    $this->submitForm([], 'Restore');
    $session = $this->assertSession();
    $session->statusCodeEquals(200);
    $session->addressEquals('admin/config/development/backup_migrate/settings/destination/backups/private_files');
    $session->pageTextContains('Restore Complete.');
  }

  /**
   * Verify that backups can be deleted.
   */
  public function testBackupsCanBeDeleted() {
    $this->testQuickBackup();

    // Load the destination page for the private files destination.
    $this->drupalGet('admin/config/development/backup_migrate/settings/destination/backups/private_files');
    $session = $this->assertSession();
    $session->statusCodeEquals(200);

    // Confirm a file exists with a "delete" link.
    $session->linkExists('Delete');

    // Load the route for deleting an existing backup.
    $this->clickLink('Delete');
    $session = $this->assertSession();
    $session->statusCodeEquals(200);
    $session->pageTextContains('Are you sure you want to delete this backup?');

    // Make sure the text without a filename is not present, which would
    // indicate that the filename was not passed correctly.
    $session->pageTextNotContains('This will permanently remove from Private Files Directory.');

    // Delete the backup.
    $this->submitForm([], 'Delete');
    $session = $this->assertSession();
    $session->statusCodeEquals(200);
    $session->addressEquals('admin/config/development/backup_migrate/settings/destination/backups/private_files');
    $session->pageTextContains('There are no backups in this destination.');
  }

}
