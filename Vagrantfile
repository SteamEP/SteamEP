Vagrant.configure("2") do |config|
  config.vm.box = "chef/centos-6.5"
  config.vm.network :forwarded_port, guest: 80, host: 80
  config.vm.network :forwarded_port, guest: 3306, host: 3306
  config.vm.provision "shell", path: "./bootstrap.sh"
  config.vm.synced_folder "./", "/var/www/html/"
end